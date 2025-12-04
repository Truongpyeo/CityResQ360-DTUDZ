#!/usr/bin/env python3
"""
Add GPL-3.0 License Headers to All Code Files
Automatically adds missing license headers to .php, .js, .ts, .py, .vue files
"""

import os
import re
from pathlib import Path

# GPL-3.0 License Header
LICENSE_HEADERS = {
    'php': '''<?php
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

''',
    'js': '''/*
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

''',
    'py': '''#!/usr/bin/env python3
"""
CityResQ360-DTUDZ - Smart City Emergency Response System
Copyright (C) 2025 DTU-DZ Team

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
"""

''',
    'go': '''/*
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

'''
}

# Same header for .ts and .vue as .js
LICENSE_HEADERS['ts'] = LICENSE_HEADERS['js']
LICENSE_HEADERS['vue'] = LICENSE_HEADERS['js']

def has_license_header(content):
    """Check if file already has license header"""
    # Check for common license indicators
    indicators = [
        'Copyright (C) 2025 DTU-DZ',
        'GNU General Public License',
        'CityResQ360-DTUDZ'
    ]
    return any(indicator in content[:1000] for indicator in indicators)

def add_license_header(file_path):
    """Add license header to file if missing"""
    ext = file_path.suffix[1:]  # Remove leading dot
    
    if ext not in LICENSE_HEADERS:
        return False
    
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Skip if already has license
        if has_license_header(content):
            return False
        
        # Get appropriate header
        header = LICENSE_HEADERS[ext]
        
        # For PHP, remove existing <?php if present
        if ext == 'php':
            content = re.sub(r'^\s*<\?php\s*\n', '', content)
        
        # For Python, remove existing shebang if present
        if ext == 'py':
            content = re.sub(r'^#!/usr/bin/env python3\s*\n', '', content)
        
        # Write new content with header
        new_content = header + content
        
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        return True
        
    except Exception as e:
        print(f"Error processing {file_path}: {e}")
        return False

def should_skip_path(file_path, base_dir):
    """Check if path should be skipped (dependencies, build folders, etc.)"""
    relative_path = str(file_path.relative_to(base_dir))
    
    # Directories to skip
    skip_dirs = [
        'node_modules',
        'vendor',
        'dist',
        'build',
        '.git',
        '.svn',
        '__pycache__',
        '.pytest_cache',
        '.venv',
        'venv',
        'env',
        'storage/framework',
        'storage/logs',
        'bootstrap/cache',
        'public/build',
        'public/hot',
        'public/storage',
        '.nuxt',
        '.output',
        'coverage',
        '.nyc_output',
        'tmp',
        'temp',
    ]
    
    # Check if any skip pattern is in the path
    for skip_dir in skip_dirs:
        if f'/{skip_dir}/' in f'/{relative_path}' or relative_path.startswith(skip_dir):
            return True
    
    return False

def main():
    """Main function to process all files"""
    base_dir = Path('/Volumes/MyVolume/Laravel/CityResQ360-DTUDZ/modules')
    
    # File extensions to process
    extensions = ['*.php', '*.js', '*.ts', '*.py', '*.vue', '*.go']
    
    total_files = 0
    updated_files = 0
    skipped_files = 0
    excluded_files = 0
    
    print("=" * 70)
    print("Adding GPL-3.0 License Headers to Code Files")
    print("=" * 70)
    
    for ext in extensions:
        files = list(base_dir.rglob(ext))
        
        for file_path in files:
            total_files += 1
            
            # Skip dependency folders
            if should_skip_path(file_path, base_dir):
                excluded_files += 1
                continue
            
            if add_license_header(file_path):
                updated_files += 1
                print(f"✅ Added: {file_path.relative_to(base_dir.parent)}")
            else:
                skipped_files += 1
    
    print("\n" + "=" * 70)
    print("SUMMARY")
    print("=" * 70)
    print(f"Total files scanned: {total_files}")
    print(f"✅ Headers added: {updated_files}")
    print(f"⏭️  Skipped (already has header): {skipped_files}")
    print(f"🚫 Excluded (dependencies/build): {excluded_files}")
    print("=" * 70)

if __name__ == "__main__":
    main()
