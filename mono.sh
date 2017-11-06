#! /bin/bash

echo "Installing mono-complete............................."

if [ "$(id -u)" != "0" ]; then
    echo "Sorry, you are not root (use sudo <command>)."
    exit 1
fi

echo "VIEW http://www.mono-project.com/download/#download-lin-ubuntu"