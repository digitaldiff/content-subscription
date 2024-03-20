# Content Subscriptions

Content Subscriptions is a plugin that allows your visitors to subscribe to your content.

Create section-based groups with individual mail templates.

## Features

- Allow page visitors to subscribe to content updates.
- Custom mail templates per group.
- Double opt-in via verification mail.
- Option to manually resend verification mail.
- Users can easily unsubscribe if they no longer wish to receive your updates. 

## Requirements

- Craft CMS 4.x.
  
## Installation

1. Open a terminal inside of your craft project
2. Load plugin via composer	
		composer require publishing/content-subscriptions
		
3. Open "Settings" then "Plugins" in your control panel. Select "Install" from the dropdown for "Content Subscriptions"

## Setup 
In the control panel, go to "Content Subscriptions" and create a new group. Here you choose which section the group should belong to and what your notification-mails look like. After saving, you'll see a twig snipped. Integrate this into your chosen twig template to display the subscription form.

## Send Notifications
After installing our extension, whenever you create or update an entry, you'll see a new option in the detail section (to the right of your entry data). This option is titled "Notify subscribers". Activate this switch and save the entry, whenever you wish to notify your subscribers. Note that this switch will always be in the off-position by default.