#!/bin/bash
# local.sh
# Este script ejecuta comandos de Warp en el directorio actual.
# Si se pasa el parámetro -g, se omiten los comandos de grunt.

# Determinar si se deben ejecutar los comandos de grunt (por defecto sí)
RUN_GRUNT=true
if [ "$1" == "-g" ]; then
    RUN_GRUNT=false
fi

echo "--------------------------------------------------"
echo "Running: warp composer install"
echo "--------------------------------------------------"
warp composer install

echo "--------------------------------------------------"
echo "Running: warp magento se:up"
echo "--------------------------------------------------"
warp magento se:up

echo "--------------------------------------------------"
echo "Running: warp magento se:di:co"
echo "--------------------------------------------------"
warp magento se:di:co

if [ "$RUN_GRUNT" = true ]; then
    echo "--------------------------------------------------"
    echo "Running: warp grunt exec"
    echo "--------------------------------------------------"
    warp grunt exec

    echo "--------------------------------------------------"
    echo "Running: warp grunt less"
    echo "--------------------------------------------------"
    warp grunt less
fi

echo "--------------------------------------------------"
echo "All commands executed."
