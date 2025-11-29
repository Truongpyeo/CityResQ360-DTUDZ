#!/bin/bash

# 📝 Add GPL v3 License Headers to Source Files
# Usage: ./scripts/add-license-headers.sh

set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}📝 Adding GPL License Headers${NC}"
echo -e "${BLUE}================================${NC}"
echo ""

# PHP Header
PHP_HEADER='<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

'

# JS Header
JS_HEADER='/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

'

# Python Header  
PYTHON_HEADER='# CityResQ360-DTUDZ - Smart City Emergency Response System
# Copyright (C) 2025 DTU-DZ Team
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <https://www.gnu.org/licenses/>.

'

# Go Header
GO_HEADER='/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

'

# Function to add header
add_header() {
    local file="$1"
    local header="$2"
    
    # Skip if already has license
    if grep -q "GNU General Public License" "$file" 2>/dev/null; then
        echo -e "${YELLOW}⏭️  Skip: ${file}${NC}"
        return
    fi
    
    # Create temp file
    temp=$(mktemp)
    
    # Handle PHP files specially
    if [[ "$file" == *.php ]]; then
        if head -n 1 "$file" | grep -q "<?php"; then
            echo "$header" > "$temp"
            tail -n +2 "$file" >> "$temp"
        else
            echo "$header" > "$temp"
            cat "$file" >> "$temp"
        fi
    else
        echo "$header" > "$temp"
        cat "$file" >> "$temp"
    fi
    
    mv "$temp" "$file"
    echo -e "${GREEN}✅ Added: ${file}${NC}"
}

count=0

# PHP FILES
echo -e "${BLUE}📄 Processing PHP files...${NC}"
while IFS= read -r file; do
    if [ -f "$file" ]; then
        add_header "$file" "$PHP_HEADER"
        ((count++))
    fi
done < <(find modules/CoreAPI/app/Models -name "*.php" -type f)

while IFS= read -r file; do
    if [ -f "$file" ]; then
        add_header "$file" "$PHP_HEADER"
        ((count++))
    fi
done < <(find modules/CoreAPI/app/Http/Controllers -name "*.php" -type f)

# Add to routes
for file in modules/CoreAPI/routes/*.php; do
    if [ -f "$file" ]; then
        add_header "$file" "$PHP_HEADER"
        ((count++))
    fi
done

# JAVASCRIPT
echo -e "${BLUE}📄 Processing JavaScript files...${NC}"
for service in MediaService NotificationService IncidentService IoTService; do
    if [ -f "modules/$service/index.js" ]; then
        add_header "modules/$service/index.js" "$JS_HEADER"
        ((count++))
    fi
    if [ -f "modules/$service/src/index.js" ]; then
        add_header "modules/$service/src/index.js" "$JS_HEADER"
        ((count++))
    fi
done

# PYTHON  
echo -e "${BLUE}📄 Processing Python files...${NC}"
for service in AIMLService FloodEyeService SearchService AnalyticsService; do
    if [ -f "modules/$service/main.py" ]; then
        add_header "modules/$service/main.py" "$PYTHON_HEADER"
        ((count++))
    fi
done

# GO
echo -e "${BLUE}📄 Processing Go files...${NC}"
if [ -f "modules/WalletService/main.go" ]; then
    add_header "modules/WalletService/main.go" "$GO_HEADER"
    ((count++))
fi

while IFS= read -r file; do
    if [ -f "$file" ]; then
        add_header "$file" "$GO_HEADER"
        ((count++))
    fi
done < <(find modules/WalletService -name "*.go" -type f -not -path "*/vendor/*")

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}✅ Added headers to $count files${NC}"
echo -e "${GREEN}================================${NC}"
