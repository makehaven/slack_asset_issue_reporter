Slack Asset Issue Reporter Module for Drupal 10

Overview:
-------------
The Slack Asset Issue Reporter module integrates Drupal 10 webforms with Slack by sending notifications when asset issues are reported. This module uses the new Webform Handler plugin system (instead of legacy hooks or Rules) to trigger notifications upon submission. It reads an "asset_nid" parameter from the URL to load the corresponding asset node and determine the appropriate Slack channel—first by checking the asset node’s Slack channel field, then by falling back to a taxonomy term’s Slack channel field. The Slack webhook URL is centrally managed by the Slack Connector module.

Features:
-------------
• Webform Handler Integration: Utilizes Drupal 10’s modern Webform plugin system to trigger Slack notifications on submission.
• Asset-Specific Notifications: Automatically reads an asset_nid from the URL (e.g., ?asset_nid=4553) to identify which asset is reporting an issue.
• Dynamic Channel Selection: Determines the Slack channel by first checking the asset node’s field_item_slack_channel; if absent, it falls back to the taxonomy term’s field_interest_slack_channel.
• Centralized Configuration: Uses the Slack Connector module for managing the Slack webhook URL, ensuring consistent integration across multiple modules.
• Customizable Message: Constructs a detailed notification message using webform submission data such as equipment name, issue type, and description.

Installation:
-------------
1. Place the Slack Asset Issue Reporter module folder in your Drupal custom modules directory (e.g., modules/custom/slack_asset_issue_reporter).
2. Ensure that the Slack Connector module and the Webform module are installed, enabled, and properly configured.
3. Enable the Slack Asset Issue Reporter module via the Drupal admin interface.
4. Add the “Slack Asset Issue Notifier” handler to your webform(s) via the webform settings UI.

Configuration:
-------------
• Slack Webhook URL: This is managed by the Slack Connector module. Configure it at /admin/config/services/slack-connector.
• Slack Channel Determination: The module looks for a Slack channel in the asset node’s field_item_slack_channel. If none is found, it checks the taxonomy term (from field_item_area_interest) for a value in field_interest_slack_channel.
• Webform Handler Settings: The handler is added to specific webforms that report asset issues, ensuring notifications are sent only from those forms.

Usage:
-------------
After configuration, when a user submits an asset issue report via a webform (for example, using a URL like:
  https://www.example.com/equipment/issue?equipment_name=Water%20Jet%20Cutter&asset_nid=4553),
the module will:
  - Load the asset node corresponding to asset_nid.
  - Determine the correct Slack channel based on the asset node’s fields.
  - Build a notification message from the submission data (including equipment name, issue type, and description).
  - Post the message to Slack using the webhook URL provided by the Slack Connector module.

Support:
-------------
For further assistance with configuration or troubleshooting, please consult your Drupal development team or refer to additional project documentation.
