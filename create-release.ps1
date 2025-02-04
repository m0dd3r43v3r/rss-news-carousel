# Function to update version in a file
function Update-Version {
    param (
        [string]$FilePath,
        [string]$OldVersion,
        [string]$NewVersion,
        [string]$Pattern
    )
    
    if (Test-Path $FilePath) {
        $content = Get-Content $FilePath -Raw
        $updatedContent = $content -replace $Pattern, "`${1}$NewVersion`${2}"
        Set-Content -Path $FilePath -Value $updatedContent -NoNewline
        Write-Host "Updated version in $FilePath from $OldVersion to $NewVersion"
    } else {
        Write-Warning "File not found: $FilePath"
    }
}

# Get current version from package.json
$packageJson = Get-Content "package.json" -Raw | ConvertFrom-Json
$currentVersion = $packageJson.version

# Ask for new version
Write-Host "Current version is: $currentVersion"
$newVersion = Read-Host "Enter new version number (press Enter to use current version)"

if ([string]::IsNullOrWhiteSpace($newVersion)) {
    $newVersion = $currentVersion
}

# Update version in package.json
$packageJson.version = $newVersion
$packageJson | ConvertTo-Json -Depth 100 | Set-Content "package.json"
Write-Host "Updated version in package.json to $newVersion"

# Update version in main plugin file
Update-Version -FilePath "rss-news-carousel.php" `
    -OldVersion $currentVersion `
    -NewVersion $newVersion `
    -Pattern "(\* Version:\s*).*(\r?\n)"

# Update version constant in main plugin file
Update-Version -FilePath "rss-news-carousel.php" `
    -OldVersion $currentVersion `
    -NewVersion $newVersion `
    -Pattern "(define\('RSS_NEWS_CAROUSEL_VERSION',\s*').*('.*\))"

# Add new version to changelog if it doesn't exist
$changelogPath = "CHANGELOG.md"
$changelog = Get-Content $changelogPath -Raw
if ($changelog -notmatch "\[\s*$newVersion\s*\]") {
    $today = Get-Date -Format "yyyy-MM-dd"
    $newEntry = @"

## [$newVersion] - $today

### Added
- 

### Changed
- 

### Fixed
- 

"@
    # Insert after the first line that contains "# Changelog"
    $changelog = $changelog -replace "(# Changelog.*?\r?\n)", "`$1$newEntry"
    Set-Content -Path $changelogPath -Value $changelog -NoNewline
    Write-Host "Added new version entry to CHANGELOG.md"
    Write-Host "Please update the changelog entries manually before creating the release."
    pause
}

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
    "CHANGELOG.md",
    "plugin-update-checker",
    "build",
    "src/frontend.js",
    "src/style.css"
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
$zipName = "rss-news-carousel-v$newVersion.zip"
if (Test-Path $zipName) {
    Remove-Item $zipName -Force
}

Compress-Archive -Path "$releaseDir\*" -DestinationPath $zipName

# Clean up
Remove-Item -Recurse -Force $releaseDir

Write-Host "`nRelease creation completed!"
Write-Host "Version updated to: $newVersion"
Write-Host "ZIP file created: $zipName"
Write-Host "`nNext steps:"
Write-Host "1. Update the changelog entries if you haven't already"
Write-Host "2. Commit all changes to Git"
Write-Host "3. Create a new release on GitHub with tag v$newVersion"
Write-Host "4. Upload $zipName to the release" 