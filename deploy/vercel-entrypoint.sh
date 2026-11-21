#!/bin/sh
set -eu

cd /app
node server.js &
node_pid=$!

cleanup() {
	kill -TERM "$node_pid" 2>/dev/null || true
}
trap cleanup EXIT TERM INT

frankenphp run --config /etc/caddy/Caddyfile &
web_pid=$!
wait "$web_pid"
