#!/bin/bash

get_ipv4_addresses() {
    ifconfig | grep -oE "\b([0-9]{1,3}\.){3}[0-9]{1,3}\b" | grep -vE "^127\.|^255\." | sort | uniq
}


# Define the list of IP addresses to test
ip_list=($(get_ipv4_addresses) $(hostname -I) 172.31.32.1)

# Define the port on which xdebug client listens
# Define the port on which xdebug client listens
port=9003

echo "Testing xdebug client on different IPs..."

# Iterate through the list of IP addresses
for ip in "${ip_list[@]}"; do
    echo -n "Testing IP: $ip..."
    result=$(nc -zvw 1 "$ip" "$port" 2>&1)
    if [[ "$result" =~ "succeeded" ]]; then
        echo -e "\e[32mXdebug client is working on IP: $ip\e[0m"
    else
        echo -e "\e[31mXdebug client is not working on IP: $ip\e[0m"
    fi
done