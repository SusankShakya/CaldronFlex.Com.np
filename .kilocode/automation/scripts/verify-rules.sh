#!/bin/bash
# Comprehensive rule verification script

set -e

echo "=== CaldronFlex Rule Verification ==="
echo

# Check if .kilocode exists
if [ ! -d ".kilocode" ]; then
    echo "ERROR: .kilocode directory not found!"
    exit 1
fi

# Function to check file exists
check_file() {
    if [ ! -f "$1" ]; then
        echo "ERROR: Required file missing: $1"
        return 1
    else
        echo "✓ Found: $1"
        return 0
    fi
}

# Check core documentation
echo "Checking core documentation..."
check_file ".kilocode/USER_RULES.md"
check_file ".kilocode/README.md"
check_file ".kilocode/implementation-plan.mdc"
echo

# Check memory banks
echo "Checking memory banks..."
check_file ".kilocode/memory-banks/MEMORY_BANK_GUIDE.md"
check_file ".kilocode/memory-banks/project-context/project-overview.md"
echo

# Check workflows
echo "Checking workflows..."
check_file ".kilocode/workflows/WORKFLOW_GUIDE.md"
check_file ".kilocode/workflows/feature-development-workflow.md"
echo

# Check code standards
echo "Checking code standards..."
check_file ".kilocode/code-standards/CODE_STANDARDS_GUIDE.md"
check_file ".kilocode/code-standards/php-standards.md"
echo

# Check for implementation plans
echo "Checking for active implementation plans..."
if ls *.mdc 1> /dev/null 2>&1; then
    for plan in *.mdc; do
        if [ "$plan" != "implementation-plan.mdc" ]; then
            echo "Found implementation plan: $plan"
            # Check if plan has completed steps
            if grep -q "\[x\] Done" "$plan"; then
                echo "  ✓ Has completed steps"
            else
                echo "  ⚠ No completed steps yet"
            fi
        fi
    done
else
    echo "No active implementation plans found"
fi
echo

# Run Python rule checker if available
if command -v python3 &> /dev/null; then
    echo "Running USER_RULES compliance check..."
    python3 .kilocode/automation/scripts/check-user-rules.py $(find . -name "*.php" -o -name "*.js" | grep -v vendor | head -10)
else
    echo "Python not available, skipping compliance check"
fi

echo
echo "=== Verification Complete ==="