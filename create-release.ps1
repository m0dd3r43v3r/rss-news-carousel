# Get the plugin version from the main PHP file
$pluginFile = Get-Content "rss-news-carousel.php" -Raw
$version = if($pluginFile -match "Version:\s*(.+)") { $matches[1] } else { "1.0.0" }

# Create a temporary directory for the release
$releaseDir = "release-tmp"
$pluginDir = "rss-news-carousel"
$fullReleaseDir = Join-Path $releaseDir $pluginDir

# Create directories if they don't exist
New-Item -ItemType Directory -Force -Path $fullReleaseDir | Out-Null

# Files and directories to include
$filesToInclude = @(
    "rss-news-carousel.php",
    "README.md",
    "plugin-update-checker",
    "build",
    "src/frontend.js"
)

# Copy files to release directory
foreach ($item in $filesToInclude) {
    if (Test-Path $item) {
        if ((Get-Item $item) -is [System.IO.DirectoryInfo]) {
            Copy-Item $item -Destination $fullReleaseDir -Recurse -Force
        } else {
            Copy-Item $item -Destination $fullReleaseDir -Force
        }
    } else {
        Write-Warning "Item not found: $item"
    }
}

# Create the ZIP file
$zipName = "rss-news-carousel-v$version.zip"
if (Test-Path $zipName) {
    Remove-Item $zipName -Force
}

Compress-Archive -Path "$releaseDir\*" -DestinationPath $zipName

# Clean up
Remove-Item -Recurse -Force $releaseDir

Write-Host "Release ZIP created: $zipName" 