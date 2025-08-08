<?php

namespace Drupal\slack_asset_issue_reporter;

use Drupal;

/**
 * Helper class for posting messages to Slack.
 */
class SlackAssetIssueNotifierHelper {

  /**
   * Posts a message to Slack.
   *
   * @param string $channel
   *   The Slack channel (e.g., "#testing").
   * @param string $message
   *   The message to send.
   *
   * @return bool
   *   TRUE if the message was posted successfully, FALSE otherwise.
   */
  public static function postToSlack($channel, $message) {
    // Load the Slack webhook URL from Slack Connector configuration.
    $connector_config = Drupal::config('slack_connector.settings');
    $webhook_url = $connector_config->get('webhook_url');

    if (empty($webhook_url)) {
      Drupal::logger('slack_asset_issue_reporter')
        ->error('Slack webhook URL is not configured in Slack Connector.');
      return FALSE;
    }

    $payload = [
      'channel' => $channel,
      'text' => $message,
    ];

    try {
      $client = Drupal::httpClient();
      $response = $client->post($webhook_url, [
        'headers' => ['Content-Type' => 'application/json'],
        'json' => $payload,
      ]);
      $response_text = $response->getBody()->getContents();
      Drupal::logger('slack_asset_issue_reporter')
        ->notice('Slack response: @response', ['@response' => $response_text]);
      return TRUE;
    }
    catch (\Exception $e) {
      Drupal::logger('slack_asset_issue_reporter')
        ->error('Error posting to Slack: @error', ['@error' => $e->getMessage()]);
      return FALSE;
    }
  }

}
