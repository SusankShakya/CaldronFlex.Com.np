#!/usr/bin/env python3
"""
Check code compliance with USER_RULES.md
"""

import sys
import re
import os

def check_file(filepath):
    """Check a single file for USER_RULES compliance"""
    violations = []
    
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
        lines = content.split('\n')
    
    # Rule #5: No apologies
    apology_patterns = [
        r'\bsorry\b', r'\bapolog', r'\bmy bad\b', r'\bmy mistake\b'
    ]
    for i, line in enumerate(lines):
        for pattern in apology_patterns:
            if re.search(pattern, line, re.IGNORECASE):
                violations.append(f"{filepath}:{i+1} - Rule #5: No apologies found")
    
    # Rule #18: Use explicit variable names
    if filepath.endswith('.php'):
        # Check for single letter variables (except common ones like $i in loops)
        bad_vars = re.findall(r'\$[a-z]\b(?!\s*=\s*0;\s*\$[a-z]\s*<)', content)
        if bad_vars:
            violations.append(f"{filepath} - Rule #18: Single letter variables found: {set(bad_vars)}")
    
    elif filepath.endswith('.js'):
        # Check for single letter variables
        bad_vars = re.findall(r'\b(?:let|const|var)\s+([a-z])\b', content)
        if bad_vars:
            violations.append(f"{filepath} - Rule #18: Single letter variables found: {set(bad_vars)}")
    
    # Rule #26: Avoid magic numbers
    magic_numbers = re.findall(r'(?<![0-9])[2-9]\d*(?![0-9])', content)
    # Filter out common acceptable uses (array indexes, HTTP codes, etc)
    magic_numbers = [n for n in magic_numbers if int(n) > 1 and int(n) not in [200, 201, 204, 301, 302, 400, 401, 403, 404, 500]]
    if magic_numbers:
        violations.append(f"{filepath} - Rule #26: Possible magic numbers found: {set(magic_numbers[:5])}")
    
    return violations

def main():
    """Main entry point"""
    files = sys.argv[1:]
    all_violations = []
    
    for filepath in files:
        if os.path.exists(filepath):
            violations = check_file(filepath)
            all_violations.extend(violations)
    
    if all_violations:
        print("USER_RULES.md violations found:")
        for violation in all_violations:
            print(f"  - {violation}")
        return 1
    
    return 0

if __name__ == "__main__":
    sys.exit(main())