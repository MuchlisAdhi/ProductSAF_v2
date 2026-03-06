param(
    [string]$OutputPath = "",
    [switch]$VerboseOutput
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

Add-Type -AssemblyName System.Drawing

function Get-FirstAvailableFontName {
    param(
        [string[]]$Candidates,
        [string]$Fallback
    )

    $installed = New-Object System.Drawing.Text.InstalledFontCollection
    $installedNames = @($installed.Families | ForEach-Object { $_.Name })

    foreach ($candidate in $Candidates) {
        if ($installedNames -contains $candidate) {
            return $candidate
        }
    }

    return $Fallback
}

function Draw-RotatedImage {
    param(
        [System.Drawing.Graphics]$Graphics,
        [System.Drawing.Image]$Image,
        [float]$X,
        [float]$Y,
        [float]$Width,
        [float]$Height,
        [float]$AngleDeg
    )

    $state = $Graphics.Save()
    try {
        $centerX = $X + ($Width / 2.0)
        $centerY = $Y + ($Height / 2.0)
        $Graphics.TranslateTransform($centerX, $centerY)
        $Graphics.RotateTransform($AngleDeg)
        $Graphics.DrawImage($Image, -($Width / 2.0), -($Height / 2.0), $Width, $Height)
    } finally {
        $Graphics.Restore($state)
    }
}

$repoRoot = Split-Path -Parent $PSScriptRoot
$assetsDir = Join-Path $repoRoot 'resources\og-assets'
$logoPath = Join-Path $repoRoot 'public\images\logo\saf-logo.png'

$burasPath = Join-Path $assetsDir 'sa-buras.png'
$br1Path = Join-Path $assetsDir 'sa-br1.png'
$qlsPath = Join-Path $assetsDir 'sa-qls.png'

$required = @($logoPath, $burasPath, $br1Path, $qlsPath)
foreach ($path in $required) {
    if (-not (Test-Path $path)) {
        throw "Asset tidak ditemukan: $path"
    }
}

if ([string]::IsNullOrWhiteSpace($OutputPath)) {
    $OutputPath = Join-Path $repoRoot 'public\images\og\saf-katalog-og.png'
}

$outputDir = Split-Path -Parent $OutputPath
New-Item -ItemType Directory -Path $outputDir -Force | Out-Null

$width = 1200
$height = 630

$bitmap = New-Object System.Drawing.Bitmap($width, $height, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
$graphics = [System.Drawing.Graphics]::FromImage($bitmap)

$logo = [System.Drawing.Image]::FromFile($logoPath)
$buras = [System.Drawing.Image]::FromFile($burasPath)
$br1 = [System.Drawing.Image]::FromFile($br1Path)
$qls = [System.Drawing.Image]::FromFile($qlsPath)

try {
    $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
    $graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
    $graphics.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit

    $canvasRect = New-Object System.Drawing.Rectangle(0, 0, $width, $height)

    $gradientBrush = New-Object System.Drawing.Drawing2D.LinearGradientBrush(
        $canvasRect,
        [System.Drawing.Color]::Black,
        [System.Drawing.Color]::Black,
        135.0
    )
    try {
        $blend = New-Object System.Drawing.Drawing2D.ColorBlend
        $blend.Colors = @(
            [System.Drawing.ColorTranslator]::FromHtml('#2d5f3f'),
            [System.Drawing.ColorTranslator]::FromHtml('#4a8c5e'),
            [System.Drawing.ColorTranslator]::FromHtml('#2d5f3f')
        )
        $blend.Positions = @(0.0, 0.5, 1.0)
        $gradientBrush.InterpolationColors = $blend
        $graphics.FillRectangle($gradientBrush, $canvasRect)
    } finally {
        $gradientBrush.Dispose()
    }

    $circleTopBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(26, 0, 0, 0))
    $circleBottomBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(13, 255, 255, 255))
    try {
        $graphics.FillEllipse($circleTopBrush, -150, -150, 400, 400)
        $graphics.FillEllipse($circleBottomBrush, 850, 430, 300, 300)
    } finally {
        $circleTopBrush.Dispose()
        $circleBottomBrush.Dispose()
    }

    $logoHaloBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(10, 255, 255, 255))
    $logoContainerBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(20, 255, 255, 255))
    $logoContainerBorder = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(46, 255, 255, 255), 2)
    try {
        $logoContainerX = 95
        $logoContainerY = 88
        $logoContainerSize = 210
        $graphics.FillEllipse($logoHaloBrush, $logoContainerX - 14, $logoContainerY - 14, $logoContainerSize + 28, $logoContainerSize + 28)
        $graphics.FillEllipse($logoContainerBrush, $logoContainerX, $logoContainerY, $logoContainerSize, $logoContainerSize)
        $graphics.DrawEllipse($logoContainerBorder, $logoContainerX, $logoContainerY, $logoContainerSize, $logoContainerSize)

        $logoSize = 150
        $logoX = $logoContainerX + (($logoContainerSize - $logoSize) / 2)
        $logoY = $logoContainerY + (($logoContainerSize - $logoSize) / 2)
        $graphics.DrawImage($logo, $logoX, $logoY, $logoSize, $logoSize)
    } finally {
        $logoHaloBrush.Dispose()
        $logoContainerBrush.Dispose()
        $logoContainerBorder.Dispose()
    }

    $antonLike = Get-FirstAvailableFontName @('Anton', 'Impact', 'Bahnschrift SemiCondensed', 'Arial Black') 'Arial Black'
    $scriptLike = Get-FirstAvailableFontName @('Kaushan Script', 'Brush Script MT', 'Segoe Script', 'Lucida Handwriting') 'Segoe Script'
    $sansLike = Get-FirstAvailableFontName @('Segoe UI', 'Arial', 'Tahoma') 'Arial'

    $smallTextFont = New-Object System.Drawing.Font($sansLike, 18, [System.Drawing.FontStyle]::Bold, [System.Drawing.GraphicsUnit]::Pixel)
    $titleFont = New-Object System.Drawing.Font($antonLike, 52, [System.Drawing.FontStyle]::Regular, [System.Drawing.GraphicsUnit]::Pixel)
    $taglineFont = New-Object System.Drawing.Font($scriptLike, 28, [System.Drawing.FontStyle]::Regular, [System.Drawing.GraphicsUnit]::Pixel)

    $textBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(255, 255, 255, 255))
    $subTextBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(230, 255, 255, 255))
    try {
        $textX = 95
        $graphics.DrawString('KATALOG PRODUK', $smallTextFont, $subTextBrush, $textX, 336)
        $graphics.DrawString('PT. SIDOAGUNG FARM', $titleFont, $textBrush, $textX, 370)
        $graphics.DrawString('Menjadi Tuan Rumah Di Negeri Sendiri', $taglineFont, $subTextBrush, $textX, 454)
    } finally {
        $smallTextFont.Dispose()
        $titleFont.Dispose()
        $taglineFont.Dispose()
        $textBrush.Dispose()
        $subTextBrush.Dispose()
    }

    $shadowBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(78, 0, 0, 0))
    try {
        $baselineY = 410
        $leftX = 546
        $centerX = 755
        $rightX = 972
        $bagWidth = 205
        $bagHeight = 226
        $centerWidth = 230
        $centerHeight = 252

        $graphics.FillEllipse($shadowBrush, $leftX + 16, $baselineY - 14, $bagWidth - 24, 26)
        $graphics.FillEllipse($shadowBrush, $centerX + 18, $baselineY - 20, $centerWidth - 28, 30)
        $graphics.FillEllipse($shadowBrush, $rightX + 16, $baselineY - 14, $bagWidth - 24, 26)

        Draw-RotatedImage -Graphics $graphics -Image $buras -X $leftX -Y ($baselineY - $bagHeight) -Width $bagWidth -Height $bagHeight -AngleDeg -5.0
        Draw-RotatedImage -Graphics $graphics -Image $br1 -X $centerX -Y ($baselineY - $centerHeight - 10) -Width $centerWidth -Height $centerHeight -AngleDeg 0.0
        Draw-RotatedImage -Graphics $graphics -Image $qls -X $rightX -Y ($baselineY - $bagHeight) -Width $bagWidth -Height $bagHeight -AngleDeg 5.0
    } finally {
        $shadowBrush.Dispose()
    }

    $bitmap.Save($OutputPath, [System.Drawing.Imaging.ImageFormat]::Png)

    if ($VerboseOutput) {
        Write-Output "OG image generated: $OutputPath"
    }
}
finally {
    $logo.Dispose()
    $buras.Dispose()
    $br1.Dispose()
    $qls.Dispose()
    $graphics.Dispose()
    $bitmap.Dispose()
}
