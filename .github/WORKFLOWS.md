# GitHub Workflows - CI/CD for QPAY

## Setup Instructions

1. Create folder: `.github/workflows/`
2. Save workflow files dalam folder tersebut
3. Workflows akan auto-trigger saat push ke GitHub

## Available Workflows

### 1. Tests (tests.yml)
Runs application tests otomatis setiap push

### 2. Code Quality (code-quality.yml)
Check code standards dan style

### 3. Security (security.yml)
Scan untuk security vulnerabilities

## Setup GitHub Actions

1. Go to https://github.com/rizqyyourin/qpay/settings/actions
2. Enable GitHub Actions
3. Configure required secrets di Settings → Secrets

## Deploy Workflow

Can be triggered manually untuk auto-deploy ke production:

1. Push code ke repository
2. GitHub Actions runs tests
3. If tests pass, can manually trigger deployment
4. Deploy script runs dan update production server
