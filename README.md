# Description

This is an API endpoint and service to deploy and configure a Plesk instance on a Vultr server.

## APIs Used

- Vultr
- Plesk

## Usage

1. List Pending Domains for setup.
    - `php artisan vp:pending-domains`

2. Choose Domain ID from the List and Provision Plesk Server.
    - `php artisan vp:provision-server --domainId={domain_id-here}`

3. Update DNS Records (manually for now).

4. Configure the Plesk Instance.
    - `php artisan vp:configure-server --domainId={domain_id-here}`

5. Install and enable Plesk Firewall.

### To Do

- [ ] Webhook to Receive Order Data
- [x] Provision Plesk Server
- [ ] Create DNS Records at Registrar
- [x] Configure Plesk User and Custom Domain
- [x] Add Domain to Plesk
- [x] Add Custom Panel SSL Certificate
- [x] Add Primary Domain SSL Certificate
- [x] Improve SSL Settings
- [x] Install WordPress Site
- [x] Enable Nginx Caching
- [x] Enable IPV4 Reverse DNS
- [ ] Install and Enable Firewall
= [ ] Record Demo Video

### QOL Changes

- Better Folder Structure
- Error Exception Handling
- Sending of Email Notifications
