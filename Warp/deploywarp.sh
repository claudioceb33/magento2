#!/bin/bash

warp magento ma:en
git stash
git pull origin -f
warp composer install
sudo chmod 664 app/etc/config.php
sudo chown -R :33 app
warp magento se:up
warp magento se:di:co
warp magento se:st:de en_US es_AR --area adminhtml -j 4
warp magento se:st:de -f es_AR --area frontend -j 4
warp elasticsearch flush
warp magento ind:rei
warp magento ma:di
