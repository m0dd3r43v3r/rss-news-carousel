#!/bin/bash

# Create directory for the update checker
mkdir -p plugin-update-checker

# Download the latest version
curl -L https://github.com/YahnisElsts/plugin-update-checker/archive/refs/heads/master.zip -o puc.zip

# Unzip the files
unzip puc.zip

# Move the required files (corrected path)
cp -r plugin-update-checker-master/* plugin-update-checker/

# Clean up
rm -rf plugin-update-checker-master
rm puc.zip 