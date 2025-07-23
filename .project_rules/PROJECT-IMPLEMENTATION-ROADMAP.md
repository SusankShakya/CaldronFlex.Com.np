# Caldron Flex Project Implementation Roadmap

## CRITICAL CLARIFICATION: Project Actual Status

### Current Reality (July 23, 2025)
- **Rise CRM 3.9.3**: Installed at d:/xamp/htdocs/CaldronFlex.Com.np
- **Sprint Status**: NO sprints have been implemented yet
- **Documentation Status**: All files in .project_rules are REQUIREMENTS to be built, not completed work
- **Architecture Requirement**: Each subdomain needs separate PHP scripts

## Project Overview

### Business Context
Caldron Flex is a printing business that needs a comprehensive management system built on Rise CRM 3.9.3. The system must handle:
- Product catalog with dynamic pricing
- Order processing with design workflow
- File management (up to 500MB files)
- Task queue system for staff
- WhatsApp integration
- Multi-subdomain architecture

### Technical Constraints
- **Hosting**: cPanel (3GB RAM, no Docker)
- **Database**: Single MySQL database
- **Storage**: 1.5TB capacity
- **Load**: 30 concurrent users, 6 daily orders
- **File Size**: Support up to 500MB uploads

## Subdomain Architecture Plan

```mermaid
graph TB
    subgraph "Domain Structure"
        MAIN[caldronflex.com.np<br/>Main CRM Platform]
        STORE[store.caldronflex.com.np<br/>E-commerce Frontend]
        API[api.caldronflex.com.np<br/>REST API Services]
        FILES[files.caldronflex.com.np<br/>File Management]
        BACKUP[bck.caldronflex.com.np<br/>Backup System]
    end
    
    subgraph "Shared Resources"
        DB[(MySQL Database<br/>Shared)]
        SESS[Session Storage<br/>Shared]
        CACHE[File Cache<br/>Shared]
    end
    
    MAIN --> DB
    STORE --> DB
    API --> DB
    FILES --> DB
    
    MAIN --> SESS
    STORE --> SESS
    API --> SESS