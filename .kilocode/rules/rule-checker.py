#!/usr/bin/env python3
"""
Main rule validation script for CaldronFlex project
"""

import sys
import json
import argparse
import os
from pathlib import Path
import yaml
import re
from datetime import datetime

class RuleChecker:
    def __init__(self, config_path=".kilocode/rules/rules-config.json"):
        self.config = self.load_config(config_path)
        self.exceptions = self.load_exceptions()
        self.custom_rules = self.load_custom_rules()
        self.violations = []
        
    def load_config(self, config_path):
        """Load rule configuration"""
        if os.path.exists(config_path):
            with open(config_path, 'r') as f:
                return json.load(f)
        return self.get_default_config()
    
    def load_exceptions(self):
        """Load rule exceptions"""
        exceptions_path = ".kilocode/rules/exceptions.json"
        if os.path.exists(exceptions_path):
            with open(exceptions_path, 'r') as f:
                return json.load(f)
        return {}
    
    def load_custom_rules(self):
        """Load custom project-specific rules"""
        custom_path = ".kilocode/rules/custom-rules.yaml"
        if os.path.exists(custom_path):
            with open(custom_path, 'r') as f:
                return yaml.safe_load(f)
        return {}
    
    def get_default_config(self):
        """Default rule configuration"""
        return {
            "code_quality": {
                "max_function_length": 50,
                "max_cyclomatic_complexity": 10,
                "min_variable_name_length": 3,
                "require_documentation": True
            },
            "security": {
                "check_sql_injection": True,
                "check_xss": True,
                "require_input_validation": True
            },
            "performance": {
                "max_query_count": 10,
                "require_caching": True,
                "max_file_size_kb": 1000
            },
            "workflow": {
                "commit_message_pattern": "^(feat|fix|docs|style|refactor|test|chore):",
                "branch_pattern": "^(feature|bugfix|hotfix|release)/",
                "min_pr_description_length": 50
            }
        }
    
    def check_all(self, directory="."):
        """Run all rule checks"""
        print("=== CaldronFlex Rule Checker ===\n")
        
        # Code quality checks
        self.check_code_quality(directory)
        
        # Security checks
        self.check_security(directory)
        
        # Performance checks
        self.check_performance(directory)
        
        # Workflow checks
        self.check_workflow()
        
        return self.generate_report()
    
    def check_code_quality(self, directory):
        """Check code quality rules"""
        print("Checking code quality rules...")
        
        for root, dirs, files in os.walk(directory):
            # Skip vendor and node_modules
            dirs[:] = [d for d in dirs if d not in ['vendor', 'node_modules', '.git']]
            
            for file in files:
                if file.endswith(('.php', '.js')):
                    filepath = os.path.join(root, file)
                    self.check_file_quality(filepath)
    
    def check_file_quality(self, filepath):
        """Check quality rules for a single file"""
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
                lines = content.split('\n')
            
            # Check function length
            self.check_function_length(filepath, lines)
            
            # Check variable naming
            self.check_variable_naming(filepath, content)
            
            # Check documentation
            self.check_documentation(filepath, content)
            
        except Exception as e:
            print(f"Error checking {filepath}: {e}")
    
    def check_function_length(self, filepath, lines):
        """Check if functions exceed maximum length"""
        max_length = self.config['code_quality']['max_function_length']
        in_function = False
        function_start = 0
        function_name = ""
        
        for i, line in enumerate(lines):
            if re.match(r'^\s*(function|def|public|private|protected)\s+\w+', line):
                if in_function and (i - function_start) > max_length:
                    self.add_violation('error', filepath, function_start, 
                                     f"Function '{function_name}' exceeds {max_length} lines")
                in_function = True
                function_start = i
                function_name = re.findall(r'function\s+(\w+)', line)[0] if 'function' in line else 'unknown'
    
    def check_variable_naming(self, filepath, content):
        """Check variable naming conventions"""
        min_length = self.config['code_quality']['min_variable_name_length']
        
        if filepath.endswith('.php'):
            # Check PHP variables
            variables = re.findall(r'\$([a-zA-Z_]\w*)', content)
            for var in variables:
                if len(var) < min_length and var not in ['i', 'j', 'k', 'x', 'y', 'z', 'e']:
                    self.add_violation('warning', filepath, None,
                                     f"Variable '${var}' is too short (min: {min_length})")
        
        elif filepath.endswith('.js'):
            # Check JavaScript variables
            variables = re.findall(r'(?:let|const|var)\s+([a-zA-Z_]\w*)', content)
            for var in variables:
                if len(var) < min_length and var not in ['i', 'j', 'k', 'x', 'y', 'z', 'e']:
                    self.add_violation('warning', filepath, None,
                                     f"Variable '{var}' is too short (min: {min_length})")
    
    def check_documentation(self, filepath, content):
        """Check if functions have documentation"""
        if not self.config['code_quality']['require_documentation']:
            return
        
        functions = re.findall(r'(function\s+\w+|public\s+function\s+\w+|private\s+function\s+\w+)', content)
        for func in functions:
            func_name = re.findall(r'function\s+(\w+)', func)[0]
            # Simple check for documentation before function
            pattern = rf'/\*\*.*?\*/\s*{re.escape(func)}'
            if not re.search(pattern, content, re.DOTALL):
                self.add_violation('warning', filepath, None,
                                 f"Function '{func_name}' lacks documentation")
    
    def check_security(self, directory):
        """Check security rules"""
        print("Checking security rules...")
        
        for root, dirs, files in os.walk(directory):
            dirs[:] = [d for d in dirs if d not in ['vendor', 'node_modules', '.git']]
            
            for file in files:
                if file.endswith(('.php', '.js')):
                    filepath = os.path.join(root, file)
                    self.check_file_security(filepath)
    
    def check_file_security(self, filepath):
        """Check security rules for a single file"""
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for SQL injection vulnerabilities
            if self.config['security']['check_sql_injection']:
                self.check_sql_injection(filepath, content)
            
            # Check for XSS vulnerabilities
            if self.config['security']['check_xss']:
                self.check_xss(filepath, content)
            
        except Exception as e:
            print(f"Error checking security for {filepath}: {e}")
    
    def check_sql_injection(self, filepath, content):
        """Check for potential SQL injection vulnerabilities"""
        # Look for direct variable interpolation in SQL queries
        patterns = [
            r'query\s*\(\s*["\'].*?\$\w+.*?["\']',  # Direct variable in query
            r'execute\s*\(\s*["\'].*?\$\w+.*?["\']',  # Direct variable in execute
            r'mysql_query\s*\(\s*["\'].*?\$\w+.*?["\']'  # Old mysql functions
        ]
        
        for pattern in patterns:
            matches = re.findall(pattern, content)
            for match in matches:
                self.add_violation('error', filepath, None,
                                 f"Potential SQL injection vulnerability: {match[:50]}...")
    
    def check_xss(self, filepath, content):
        """Check for potential XSS vulnerabilities"""
        # Look for unescaped output
        patterns = [
            r'echo\s+\$_(?:GET|POST|REQUEST)',  # Direct echo of user input
            r'print\s+\$_(?:GET|POST|REQUEST)',  # Direct print of user input
            r'innerHTML\s*=\s*[^"\']*\$',  # Direct innerHTML assignment
        ]
        
        for pattern in patterns:
            matches = re.findall(pattern, content)
            for match in matches:
                self.add_violation('error', filepath, None,
                                 f"Potential XSS vulnerability: {match}")
    
    def check_performance(self, directory):
        """Check performance rules"""
        print("Checking performance rules...")
        
        for root, dirs, files in os.walk(directory):
            dirs[:] = [d for d in dirs if d not in ['vendor', 'node_modules', '.git']]
            
            for file in files:
                filepath = os.path.join(root, file)
                # Check file size
                if os.path.getsize(filepath) > self.config['performance']['max_file_size_kb'] * 1024:
                    self.add_violation('warning', filepath, None,
                                     f"File exceeds size limit ({self.config['performance']['max_file_size_kb']}KB)")
    
    def check_workflow(self):
        """Check workflow rules"""
        print("Checking workflow rules...")
        # This would typically check git commit messages, branch names, etc.
        # For now, we'll just print a message
        print("  - Workflow checks require git integration")
    
    def add_violation(self, level, file, line, message):
        """Add a rule violation"""
        violation = {
            'level': level,
            'file': file,
            'line': line,
            'message': message
        }
        
        # Check if this violation is in exceptions
        if not self.is_exception(violation):
            self.violations.append(violation)
    
    def is_exception(self, violation):
        """Check if a violation is in the exceptions list"""
        for exception in self.exceptions.get('exceptions', []):
            if (exception.get('file') == violation['file'] and
                exception.get('message') == violation['message']):
                return True
        return False
    
    def generate_report(self, format='text'):
        """Generate violation report"""
        if format == 'text':
            return self.generate_text_report()
        elif format == 'html':
            return self.generate_html_report()
        elif format == 'json':
            return json.dumps(self.violations, indent=2)
    
    def generate_text_report(self):
        """Generate text report"""
        print("\n=== Rule Violation Report ===\n")
        
        if not self.violations:
            print("✓ No violations found!")
            return 0
        
        # Group by level
        errors = [v for v in self.violations if v['level'] == 'error']
        warnings = [v for v in self.violations if v['level'] == 'warning']
        info = [v for v in self.violations if v['level'] == 'info']
        
        if errors:
            print(f"ERRORS ({len(errors)}):")
            for v in errors:
                print(f"  ✗ {v['file']}: {v['message']}")
        
        if warnings:
            print(f"\nWARNINGS ({len(warnings)}):")
            for v in warnings:
                print(f"  ⚠ {v['file']}: {v['message']}")
        
        if info:
            print(f"\nINFO ({len(info)}):")
            for v in info:
                print(f"  ℹ {v['file']}: {v['message']}")
        
        print(f"\nTotal violations: {len(self.violations)}")
        return 1 if errors else 0
    
    def generate_html_report(self):
        """Generate HTML report"""
        html = f"""
<!DOCTYPE html>
<html>
<head>
    <title>CaldronFlex Rule Violation Report</title>
    <style>
        body {{ font-family: Arial, sans-serif; margin: 20px; }}
        .error {{ color: red; }}
        .warning {{ color: orange; }}
        .info {{ color: blue; }}
        table {{ border-collapse: collapse; width: 100%; }}
        th, td {{ border: 1px solid #ddd; padding: 8px; text-align: left; }}
        th {{ background-color: #f2f2f2; }}
    </style>
</head>
<body>
    <h1>CaldronFlex Rule Violation Report</h1>
    <p>Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}</p>
    
    <table>
        <tr>
            <th>Level</th>
            <th>File</th>
            <th>Line</th>
            <th>Message</th>
        </tr>
"""
        
        for v in self.violations:
            html += f"""
        <tr class="{v['level']}">
            <td>{v['level'].upper()}</td>
            <td>{v['file']}</td>
            <td>{v['line'] or '-'}</td>
            <td>{v['message']}</td>
        </tr>
"""
        
        html += """
    </table>
</body>
</html>
"""
        
        with open('rule-violations.html', 'w') as f:
            f.write(html)
        
        print("HTML report generated: rule-violations.html")
        return len([v for v in self.violations if v['level'] == 'error'])

def main():
    parser = argparse.ArgumentParser(description='CaldronFlex Rule Checker')
    parser.add_argument('--all', action='store_true', help='Run all checks')
    parser.add_argument('--rule', type=str, help='Check specific rule')
    parser.add_argument('--report', type=str, choices=['text', 'html', 'json'], 
                       default='text', help='Report format')
    parser.add_argument('--dir', type=str, default='.', help='Directory to check')
    
    args = parser.parse_args()
    
    checker = RuleChecker()
    
    if args.all:
        exit_code = checker.check_all(args.dir)
        sys.exit(exit_code)
    elif args.rule:
        print(f"Checking rule: {args.rule}")
        # Implement specific rule checking
    else:
        parser.print_help()

if __name__ == "__main__":
    main()