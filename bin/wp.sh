#!/bin/bash

if [ ! -d wp ]; then mkdir wp; fi

if [ ! -d wp/wp-includes ]; then wget https://wordpress.org/latest.tar.gz; tar -xzf latest.tar.gz -C wp --strip-components=1; rm latest.tar.gz; fi