#!/bin/bash

function rabbit_help_usage()
{

    warp_message ""
    warp_message_info "Usage:"
    warp_message      " warp rabbit [options]"
    warp_message ""

    warp_message ""
    warp_message_info "Options:"
    warp_message_info   " -h, --help         $(warp_message 'display this help message')"
    warp_message ""

    warp_message_info "Available commands:"

    warp_message_info   " info               $(warp_message 'display info available')"
    warp_message_info   " ssh                $(warp_message 'connect to rabbitmq by ssh')"


    warp_message ""
    warp_message_info "Help:"
    warp_message " rabbitmq service used in ports 5672 inside containers"
    warp_message " for more information about rabbit you can access the following link: https://www.rabbitmq.com/"

    warp_message ""

    warp_message_info "Example:"
    warp_message " warp rabbit --help"
    warp_message " warp rabbit ssh --help"
    warp_message ""    

}

function rabbit_help()
{
    warp_message_info   " rabbit             $(warp_message 'service of rabbit')"
}

rabbitmq_ssh_help() {

    warp_message ""
    warp_message_info "Usage:"
    warp_message      " warp rabbit ssh [options]"
    warp_message ""

    warp_message ""
    warp_message_info "Options:"
    warp_message_info   " -h, --help         $(warp_message 'display this help message')"
    warp_message_info   " --rabbitmq         $(warp_message 'inside container rabbitmq as rabbitmq user')"
    warp_message_info   " --root             $(warp_message 'inside container rabbitmq as root user')"
    warp_message ""

    warp_message ""
    warp_message_info "Help:"
    warp_message " Connect to rabbitmq by ssh "
    warp_message ""

    warp_message_info "Example:"
    warp_message " warp rabbit ssh"
    warp_message " warp rabbit ssh --root"
    warp_message " warp rabbit ssh -h"
    warp_message " warp rabbit ssh --help"
    warp_message ""
}