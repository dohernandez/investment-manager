# investment-manager

Symfony application to manage investment.

## Table of Contents
- [Getting started](#getting-started)
    - [Prerequisites](#prerequisites)
    - [Installation](#installation)
    - [Development](#development)
- [Troubleshooting](#troubleshooting)
    - [Known issues](#known-issues)
- [Resources](resources)
    
## Getting started

### Prerequisites

You need to make sure that you have  `docker` installed

```
$ which docker
/usr/local/bin/docker
```

There is no other prerequisite needed in order to setup this project for development.
### Installation

1. Create a `.env` from the `.env.dist` file. Adapt it according to the symfony application

```bash
cp .env.dist .env
```
    
2. Build/run containers with (with and without detached mode)

```bash
docker-compose build
docker-compose up -d
```
