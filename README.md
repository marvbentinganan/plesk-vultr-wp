# Description

This is an API endpoint and service to deploy and configure a Plesk instance on a Vultr server.

# To Do
Create Migrations

customers
- email
- domain
- server_id
- plesk_instance_id

servers
- server_id
- provider_id
- customer_id
- default_password
- hostname
- ip_address
- plan
- region
- status
- created_at
- updated_at

plesk_instances
- plesk_instance_id
- server_id
- api_key
- temporary_domain
- custom_domain
- created_at
- updated_at