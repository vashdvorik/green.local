param(
    [string]$BaseUrl = "http://green.local",
    [string]$OutputRoot = "storage/app/visual-audit",
    [string]$PageFilter = "",
    [string]$LanguageFilter = "",
    [string]$ViewportFilter = "",
    [switch]$Full
)

$ErrorActionPreference = "Stop"
$script:CdpCommandId = 0

$chromeCandidates = @(
    "C:\Program Files\Google\Chrome\Application\chrome.exe",
    "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe",
    "C:\Program Files\Microsoft\Edge\Application\msedge.exe",
    "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
)
$chrome = $chromeCandidates | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1
if (-not $chrome) { throw "Chrome or Edge was not found in a standard installation directory." }

$allPages = @(
    [pscustomobject]@{ Slug = "home"; Path = "/" },
    [pscustomobject]@{ Slug = "about-project"; Path = "/about/project" },
    [pscustomobject]@{ Slug = "about-mission"; Path = "/about/mission" },
    [pscustomobject]@{ Slug = "about-directions"; Path = "/about/directions" },
    [pscustomobject]@{ Slug = "about-audits"; Path = "/about/audits" },
    [pscustomobject]@{ Slug = "about-results"; Path = "/about/results" },
    [pscustomobject]@{ Slug = "about-reports"; Path = "/about/reports" },
    [pscustomobject]@{ Slug = "about-experts"; Path = "/about/experts" },
    [pscustomobject]@{ Slug = "business"; Path = "/business" },
    [pscustomobject]@{ Slug = "news"; Path = "/news" },
    [pscustomobject]@{ Slug = "stories"; Path = "/stories" },
    [pscustomobject]@{ Slug = "media-photos"; Path = "/media/photos" },
    [pscustomobject]@{ Slug = "media-videos"; Path = "/media/videos" },
    [pscustomobject]@{ Slug = "media-catalogues"; Path = "/media/catalogues" },
    [pscustomobject]@{ Slug = "partners"; Path = "/partners" },
    [pscustomobject]@{ Slug = "contacts"; Path = "/contacts" }
)
$quickSlugs = @("home", "about-project", "business", "news", "stories", "contacts")
$pages = if ($Full) { $allPages } else { $allPages | Where-Object { $quickSlugs -contains $_.Slug } }
$languages = @("ru", "ro", "en")
$viewports = @(
    [pscustomobject]@{ Name = "desktop"; Width = 1440; Height = 900; MinGap = 56; MaxGap = 68 },
    [pscustomobject]@{ Name = "mobile"; Width = 390; Height = 844; MinGap = 40; MaxGap = 52 }
)
if ($PageFilter) { $pages = $pages | Where-Object { $_.Slug -eq $PageFilter -or $_.Path -eq $PageFilter } }
if ($LanguageFilter) { $languages = $languages | Where-Object { $_ -eq $LanguageFilter } }
if ($ViewportFilter) { $viewports = $viewports | Where-Object { $_.Name -eq $ViewportFilter } }
if (-not $pages -or -not $languages -or -not $viewports) { throw "The visual-audit filters produced no cases." }

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss-fff"
$outputDirectory = Join-Path $OutputRoot $timestamp
$profileDirectory = Join-Path $outputDirectory "chrome-profile"
New-Item -ItemType Directory -Path $profileDirectory -Force | Out-Null
$outputDirectory = (Resolve-Path -LiteralPath $outputDirectory).Path
$profileDirectory = (Resolve-Path -LiteralPath $profileDirectory).Path
$base = $BaseUrl.TrimEnd("/")

function Invoke-CdpCommand {
    param(
        [System.Net.WebSockets.ClientWebSocket]$Socket,
        [string]$Method,
        [object]$Parameters = $null
    )

    $script:CdpCommandId++
    $commandId = $script:CdpCommandId
    $payload = @{ id = $commandId; method = $Method }
    if ($null -ne $Parameters) { $payload.params = $Parameters }
    $json = $payload | ConvertTo-Json -Compress -Depth 12
    $bytes = [Text.Encoding]::UTF8.GetBytes($json)
    $sendSegment = [ArraySegment[byte]]::new($bytes)
    $Socket.SendAsync($sendSegment, [Net.WebSockets.WebSocketMessageType]::Text, $true, [Threading.CancellationToken]::None).GetAwaiter().GetResult() | Out-Null

    while ($true) {
        $buffer = New-Object byte[] 65536
        $stream = New-Object IO.MemoryStream
        do {
            $receiveSegment = [ArraySegment[byte]]::new($buffer)
            $message = $Socket.ReceiveAsync($receiveSegment, [Threading.CancellationToken]::None).GetAwaiter().GetResult()
            if ($message.MessageType -eq [Net.WebSockets.WebSocketMessageType]::Close) { throw "Chrome DevTools connection closed unexpectedly." }
            $stream.Write($buffer, 0, $message.Count)
        } while (-not $message.EndOfMessage)

        $responseText = [Text.Encoding]::UTF8.GetString($stream.ToArray())
        $stream.Dispose()
        $response = $responseText | ConvertFrom-Json
        if ($response.id -ne $commandId) { continue }
        if ($response.error) { throw "Chrome DevTools error for $Method`: $($response.error.message)" }
        return $response.result
    }
}

function Get-QaAttribute {
    param([string]$Dom, [string]$Name)
    $match = [regex]::Match($Dom, "data-qa-$Name=`"([^`"]*)`"")
    if ($match.Success) { return $match.Groups[1].Value }
    return $null
}

function Convert-QaNumber {
    param([string]$Value)
    if ([string]::IsNullOrWhiteSpace($Value) -or $Value -eq "na") { return $null }
    return [double]::Parse($Value, [Globalization.CultureInfo]::InvariantCulture)
}

$cases = New-Object System.Collections.Generic.List[object]
foreach ($page in $pages) {
    foreach ($language in $languages) {
        foreach ($viewport in $viewports) {
            $cases.Add([pscustomobject]@{ Page = $page; Language = $language; Viewport = $viewport; Menu = "closed" })
        }
    }
}
$mobileViewport = $viewports | Where-Object { $_.Name -eq "mobile" } | Select-Object -First 1
if ($mobileViewport) {
    foreach ($page in ($pages | Where-Object { $_.Slug -in @("home", "business") })) {
        foreach ($language in $languages) {
            $cases.Add([pscustomobject]@{ Page = $page; Language = $language; Viewport = $mobileViewport; Menu = "open" })
        }
    }
}

$debugPort = Get-Random -Minimum 9300 -Maximum 9900
$browserArguments = @(
    "--headless=new",
    "--disable-gpu",
    "--disable-extensions",
    "--disable-background-networking",
    "--no-first-run",
    "--no-default-browser-check",
    "--allow-insecure-localhost",
    "--ignore-certificate-errors",
    "--remote-allow-origins=*",
    "--remote-debugging-port=$debugPort",
    "--user-data-dir=$profileDirectory",
    "about:blank"
)
$browserProcess = Start-Process -FilePath $chrome -ArgumentList $browserArguments -WindowStyle Hidden -PassThru
$socket = $null
$results = New-Object System.Collections.Generic.List[object]

try {
    $targets = $null
    for ($attempt = 0; $attempt -lt 100; $attempt++) {
        try {
            $targets = Invoke-RestMethod -Uri "http://127.0.0.1:$debugPort/json/list" -TimeoutSec 1
            if ($targets) { break }
        } catch {
            Start-Sleep -Milliseconds 100
        }
    }
    $target = $targets | Where-Object { $_.type -eq "page" } | Select-Object -First 1
    if (-not $target) { throw "Chrome DevTools page target was not created." }

    $socket = [Net.WebSockets.ClientWebSocket]::new()
    $socket.ConnectAsync([Uri]$target.webSocketDebuggerUrl, [Threading.CancellationToken]::None).GetAwaiter().GetResult() | Out-Null
    Invoke-CdpCommand $socket "Page.enable" | Out-Null
    Invoke-CdpCommand $socket "Runtime.enable" | Out-Null

    foreach ($case in $cases) {
        $page = $case.Page
        $viewport = $case.Viewport
        $menuQuery = if ($case.Menu -eq "open") { "&qa-menu=open" } else { "" }
        $url = "$base$($page.Path)?qa=1&lang=$($case.Language)$menuQuery"
        $fileStem = "$($page.Slug)-$($case.Language)-$($viewport.Name)-menu-$($case.Menu)"
        $screenshotPath = Join-Path $outputDirectory "$fileStem.png"

        Invoke-CdpCommand $socket "Emulation.setDeviceMetricsOverride" @{
            width = $viewport.Width
            height = $viewport.Height
            deviceScaleFactor = 1
            mobile = ($viewport.Name -eq "mobile")
            screenWidth = $viewport.Width
            screenHeight = $viewport.Height
        } | Out-Null
        Invoke-CdpCommand $socket "Emulation.setTouchEmulationEnabled" @{
            enabled = ($viewport.Name -eq "mobile")
            maxTouchPoints = $(if ($viewport.Name -eq "mobile") { 5 } else { 1 })
        } | Out-Null
        Invoke-CdpCommand $socket "Page.navigate" @{ url = $url } | Out-Null

        $pageReady = $false
        for ($attempt = 0; $attempt -lt 120; $attempt++) {
            $readyResult = Invoke-CdpCommand $socket "Runtime.evaluate" @{
                expression = "document.readyState === 'complete' && document.documentElement.dataset.qaReady === 'true'"
                returnByValue = $true
            }
            if ($readyResult.result.value -eq $true) { $pageReady = $true; break }
            Start-Sleep -Milliseconds 100
        }
        if (-not $pageReady) { throw "The page did not become ready for visual audit: $url" }

        Invoke-CdpCommand $socket "Runtime.evaluate" @{
            expression = "new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(() => resolve(true))))"
            awaitPromise = $true
            returnByValue = $true
        } | Out-Null
        $domResult = Invoke-CdpCommand $socket "Runtime.evaluate" @{
            expression = "document.documentElement.outerHTML"
            returnByValue = $true
        }
        $dom = [string]$domResult.result.value
        $screenshotResult = Invoke-CdpCommand $socket "Page.captureScreenshot" @{
            format = "png"
            fromSurface = $true
            captureBeyondViewport = $false
        }
        [IO.File]::WriteAllBytes($screenshotPath, [Convert]::FromBase64String($screenshotResult.data))

        $ready = Get-QaAttribute $dom "ready"
        $actualViewportWidth = Convert-QaNumber (Get-QaAttribute $dom "viewport-width")
        $actualViewportHeight = Convert-QaNumber (Get-QaAttribute $dom "viewport-height")
        $overflow = Convert-QaNumber (Get-QaAttribute $dom "horizontal-overflow")
        $headerGap = Convert-QaNumber (Get-QaAttribute $dom "header-gap")
        $menuViewport = Get-QaAttribute $dom "menu-viewport"
        $scrollLock = Get-QaAttribute $dom "scroll-lock"
        $newsWidthSpread = Convert-QaNumber (Get-QaAttribute $dom "news-width-spread")
        $newsMediaSpread = Convert-QaNumber (Get-QaAttribute $dom "news-media-spread")
        $opportunityWidthSpread = Convert-QaNumber (Get-QaAttribute $dom "opportunity-width-spread")
        $opportunityMediaSpread = Convert-QaNumber (Get-QaAttribute $dom "opportunity-media-spread")

        $failures = New-Object System.Collections.Generic.List[string]
        if ($ready -ne "true") { $failures.Add("QA metrics were not produced") }
        if ($actualViewportWidth -ne $viewport.Width -or $actualViewportHeight -ne $viewport.Height) {
            $failures.Add("browser viewport: ${actualViewportWidth}x${actualViewportHeight}; expected $($viewport.Width)x$($viewport.Height)")
        }
        if ($null -eq $overflow -or $overflow -gt 1) { $failures.Add("horizontal overflow: $overflow") }
        if ($page.Path -ne "/") {
            if ($null -eq $headerGap -or $headerGap -lt $viewport.MinGap -or $headerGap -gt $viewport.MaxGap) {
                $failures.Add("header/content gap: $headerGap px; expected $($viewport.MinGap)-$($viewport.MaxGap) px")
            }
        }
        if ($menuViewport -ne "true") { $failures.Add("mobile menu is not viewport-bound") }
        if ($scrollLock -ne "true") { $failures.Add("page scroll is not locked while the menu is open") }
        foreach ($spread in @($newsWidthSpread, $newsMediaSpread, $opportunityWidthSpread, $opportunityMediaSpread)) {
            if ($null -ne $spread -and $spread -gt 2) { $failures.Add("repeated card geometry spread: $spread px") }
        }
        if (-not (Test-Path -LiteralPath $screenshotPath)) { $failures.Add("screenshot was not created") }

        $passed = $failures.Count -eq 0
        $results.Add([pscustomobject]@{
            Page = $page.Path
            Language = $case.Language
            Viewport = "$($viewport.Width)x$($viewport.Height)"
            Menu = $case.Menu
            Passed = $passed
            ActualViewport = "${actualViewportWidth}x${actualViewportHeight}"
            HorizontalOverflow = $overflow
            HeaderGap = $headerGap
            NewsWidthSpread = $newsWidthSpread
            NewsMediaSpread = $newsMediaSpread
            OpportunityWidthSpread = $opportunityWidthSpread
            OpportunityMediaSpread = $opportunityMediaSpread
            Screenshot = $screenshotPath
            Failures = @($failures)
        })
        $status = if ($passed) { "PASS" } else { "FAIL" }
        Write-Output "$status`t$($page.Path)`t$($case.Language)`t$($viewport.Name)`tmenu=$($case.Menu)"
    }
} finally {
    if ($socket -and $socket.State -eq [Net.WebSockets.WebSocketState]::Open) {
        try { Invoke-CdpCommand $socket "Browser.close" | Out-Null } catch { }
        $socket.Dispose()
    }
    if ($browserProcess -and -not $browserProcess.HasExited) {
        Start-Sleep -Milliseconds 250
        if (-not $browserProcess.HasExited) { Stop-Process -Id $browserProcess.Id -Force }
    }
}

$summaryPath = Join-Path $outputDirectory "summary.json"
$results | ConvertTo-Json -Depth 5 | Set-Content -LiteralPath $summaryPath -Encoding utf8
$failedResults = @($results | Where-Object { -not $_.Passed })
Write-Output "Audit output: $outputDirectory"
Write-Output "Cases: $($results.Count); failed: $($failedResults.Count)"
if ($failedResults.Count -gt 0) { exit 1 }
