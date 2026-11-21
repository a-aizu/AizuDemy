#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"
umask 022

/usr/bin/python3 "$SCRIPT_DIR/daily_publisher.py" "$@"

if [[ "$(hostname)" != "aizuvm" && -d "$SCRIPT_DIR/../assets/uploads" ]]; then
    rsync --chmod=D755,F644 -avz -e "ssh -i /home/indah/.ssh/id_ed25519" "$SCRIPT_DIR/../assets/uploads/" aizu@103.59.95.209:/home/aizudemy.net/public_html/assets/uploads/ 2>/dev/null || true
fi
