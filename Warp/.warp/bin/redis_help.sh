#!/bin/bash

function redis_help_usage()
{

    warp_message ""
    warp_message_info "Usage:"
    warp_message      " warp redis command [options] [arguments]"
    warp_message ""

    warp_message ""
    warp_message_info "Options:"
    warp_message_info   " -h, --help         $(warp_message 'display this help message')"
    warp_message ""

    warp_message_info "Available commands:"

    warp_message_info   " cli                $(warp_message 'run the redis-cli command inside the redis container')"
    warp_message_info   " monitor            $(warp_message 'run the monitor command inside the redis container')"
    warp_message_info   " ssh                $(warp_message 'connect to redis services by ssh')"
    warp_message_info   " flush              $(warp_message 'flush redis services data')"

    warp_message ""
    warp_message_info "Help:"
    warp_message " redis service used in ports 6379 inside containers"
    warp_message " for more information about redis you can access the following link: https://redis.io/"

    warp_message ""

    warp_message_info "Example:"
    warp_message " warp redis cli --help"
    warp_message " warp redis monitor --help"
    warp_message " warp redis ssh --help"
    warp_message " warp redis ssh cache"
    warp_message " warp redis cli session"
    warp_message " warp redis monitor cache"
    warp_message " warp redis flush cache"
    warp_message ""    

}

function redis_monitor_help_usage()
{
    warp_message ""
    warp_message_info "Usage:"
    warp_message      " warp redis monitor [options]"
    warp_message ""

    warp_message ""
    warp_message_info "Options:"
    warp_message_info   " -h, --help         $(warp_message 'display this help message')"
    warp_message ""

    warp_message ""
    warp_message_info "Help:"
    warp_message " monitor is a debugging command that streams back every command processed by the Redis server."
    warp_message " It can help in understanding what is happening to the database."
    warp_message " for more information about redis you can access the following link: https://redis.io/commands/monitor"

    warp_message ""

    warp_message_info "Example:"
    warp_message " warp redis monitor fpc"
    warp_message " warp redis monitor session"
    warp_message " warp redis monitor cache"
    warp_message ""    

}


function redis_cli_help_usage()
{

    warp_message ""
    warp_message_info "Usage:"
    warp_message      " warp redis cli [options]"
    warp_message ""

    warp_message ""
    warp_message_info "Options:"
    warp_message_info   " -h, --help         $(warp_message 'display this help message')"
    warp_message ""

    warp_message ""
    warp_message_info "Help:"
    warp_message " redis-cli is the Redis command line interface, a simple program that allows to send commands to Redis,"
    warp_message " and read the replies sent by the server, directly from the terminal."
    warp_message " for more information about redis you can access the following link: https://redis.io/topics/rediscli"

    warp_message ""

    warp_message_info "Example:"
    warp_message " warp redis cli fpc"
    warp_message " warp redis cli session"
    warp_message " warp redis cli cache"
    warp_message ""    

}

function redis_help()
{
    warp_message_info   " redis              $(warp_message 'service of redis')"
}

redis_ssh_help() {
    warp_message ""
    warp_message_info "Usage:"
    warp_message      " warp redis ssh [service] [options]"
    warp_message ""

    warp_message ""
    warp_message_info "Service:"
    warp_message_info   " cache              $(warp_message 'inside container redis-cache')"
    warp_message_info   " session            $(warp_message 'inside container redis-session')"
    warp_message_info   " fpc                $(warp_message 'inside container redis-fpc')"
    warp_message ""

    warp_message ""
    warp_message_info "Options:"
    warp_message_info   " --redis            $(warp_message 'inside container redis as redis user')"
    warp_message_info   " --root             $(warp_message 'inside container redis as root user')"
    warp_message ""

    warp_message ""
    warp_message_info "Help:"
    warp_message " Connect to redis service by ssh "
    warp_message ""

    warp_message_info "Example:"
    warp_message " warp redis ssh cache"
    warp_message " warp redis ssh cache --root"
    warp_message " warp redis ssh cache --redis"
    warp_message " warp redis ssh session"
    warp_message " warp redis ssh session --root"
    warp_message " warp redis ssh fpc"
    warp_message " warp redis ssh fpc --root"
    warp_message " warp redis ssh -h"
    warp_message ""
}

redis_flush_help() {
    warp_message ""
    warp_message_info "Usage:"
    warp_message      " warp redis flush [options]"
    warp_message ""

    warp_message ""
    warp_message_info "Options:"
    warp_message_info   " -h, --help         $(warp_message 'display this help message')"
    warp_message_info   " cache              $(warp_message 'flush data on redis cache service')"
    warp_message_info   " session            $(warp_message 'flush data on redis session service')"
    warp_message_info   " fpc                $(warp_message 'flush data on redis fpc service')"
    warp_message_info   " --all              $(warp_message 'flush data on all redis services')"
    warp_message ""

    warp_message ""
    warp_message_info "Help:"
    warp_message " redis-cli is the Redis command line interface, a simple program that allows to send commands to Redis,"
    warp_message " and read the replies sent by the server, directly from the terminal."
    warp_message " for more information about redis you can access the following link: https://redis.io/topics/rediscli"

    warp_message ""

    warp_message_info "Example:"
    warp_message " warp redis flush fpc"
    warp_message " warp redis flush session"
    warp_message " warp redis flush cache"
    warp_message " warp redis flush --all"
    warp_message " warp redis flush --help"
    warp_message ""    
}