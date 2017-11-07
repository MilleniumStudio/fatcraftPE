#!/bin/bash

echo "Installing PockerMineMP load balancer"

mkdir install
cp -r template/* install

cp -r ../../tools/FatForward/binaries/* install/
