# Description

This is the agent that collects data from various sources. This is composed of the following Services.

- WordPress Agent
- Sitespeed Metrics
- SnipeIT Assets

## Installation

- Clone project and update the `.env`. Make sure to update the SnipeIT database connection as we need to pull the domains and servers from SnipeIT.
- Install dependencies. `composer install`
- Run the Migrations. `php artisan migrate`
- Run the Seeders. `php artisan db:seed`
- Import and Process data from SnipeIT

## Services

### Snipe IT

This service retrieves the Servers and Domains from SnipeIT.

#### Available Artisan Commands

- `php artisan snipe:import-all-assets` - import assets
- `php artisan snipe:process-all-assets` - process imported assets

### WordPress Agent

This service receives the collected data by the wp agent and parses it to the appropriate tables.

#### Available Artisan Commands

- `php artisan wca:process-agent-data` - process the data from import_agents_data. This is automatically run daily if the scheduler is setup.

### SiteSpeed

This service dispaches a job to scan domains using sitespeed.io. Make sure to set the `sitespeed_check` to `true` in the domains table for sites you want to scan.

#### Available Artisan Commands

- `php artisan wca:sitespeed-scan` - dispatches a job to run a sitespeed scan for every domain that has `sitespeed_check` set to true in the domains table. You can also provide an argument of the `domain_id` of the domain you want to scan.
- `php artisan wca:sitespeed-process` - this will process the data in `import_sitespeed_data` table to store the metrics for each domain.