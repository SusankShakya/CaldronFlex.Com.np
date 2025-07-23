# Project Overview

## CaldronFlex Multi-Platform System

**Last Updated**: 2025-07-23

### System Architecture
CaldronFlex is a multi-subdomain architecture system consisting of:

1. **Main Store** (public_html/) - Primary e-commerce platform
2. **CRM Platform** (clients/) - RISE CRM v3.9.4 for client management
3. **API Services** (api/) - Shared API endpoints
4. **File Storage** (files/) - Centralized file management
5. **Backup System** (backup/) - Automated backup storage

### Key Features
- Multi-tenant architecture with subdomain routing
- Shared session management across platforms
- Integrated authentication system
- Unified file storage
- Variant and pricing management systems

### Technical Stack
- PHP (CodeIgniter 4)
- MySQL Database
- Apache with virtual hosts
- JavaScript (jQuery)
- Bootstrap CSS Framework

### Development Principles
- Follow .project_rules/USER_RULES.md strictly
- Maintain backward compatibility
- Security-first approach
- Performance optimization
- Modular architecture