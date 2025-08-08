<?php

namespace Drupal\slack_asset_issue_reporter\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\slack_asset_issue_reporter\SlackAssetIssueNotifierHelper;
use Drupal;

/**
 * Webform handler to post asset issue reports to Slack.
 *
 * @WebformHandler(
 *   id = "slack_asset_issue_notifier",
 *   label = @Translation("Slack Asset Issue Notifier"),
 *   category = @Translation("Notification"),
 *   description = @Translation("Sends a Slack notification when an asset issue is reported via a webform."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class SlackAssetIssueNotifier extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = FALSE) {
    // Get the submission data.
    $data = $webform_submission->getData();

    // Retrieve asset_nid from the URL.
    $asset_nid = Drupal::request()->query->get('asset_nid');
    $channel = '';

    if ($asset_nid) {
      $node = Drupal::entityTypeManager()->getStorage('node')->load($asset_nid);
      if ($node) {
        // Prefer the item's own Slack channel.
        if (!$node->get('field_item_slack_channel')->isEmpty()) {
          $channel = $node->get('field_item_slack_channel')->value;
        }
        // Fallback to taxonomy term Slack channel.
        elseif (!$node->get('field_item_area_interest')->isEmpty()) {
          $term = $node->get('field_item_area_interest')->entity;
          if ($term && !$term->get('field_interest_slack_channel')->isEmpty()) {
            $channel = $term->get('field_interest_slack_channel')->value;
          }
        }
      }
    }

    // Set a default channel if none was determined.
    if (empty($channel)) {
      $channel = '#default-channel';
    } else {
      // Ensure the channel string has a '#' prefix.
      if (strpos($channel, '#') !== 0) {
        $channel = '#' . $channel;
      }
    }

    // Build the message.
    $equipment_name = $data['equipment_name'] ?? 'N/A';
    $issue_type = $data['issue_type'] ?? 'N/A';
    $description = $data['describe_what_is_wrong'] ?? 'N/A';

    $message = "New equipment issue reported:\n";
    $message .= "*Equipment Name:* $equipment_name\n";
    $message .= "*Issue Type:* $issue_type\n";
    $message .= "*Description:* $description\n";

    // Post to Slack using the helper.
    $result = SlackAssetIssueNotifierHelper::postToSlack($channel, $message);

    // Prepare a clickable channel link if a workspace URL is set.
    $workspace_url = Drupal::config('slack_connector.settings')->get('workspace_url');
    if (!empty($workspace_url)) {
      // Ensure workspace URL ends with a slash.
      if (substr($workspace_url, -1) !== '/') {
        $workspace_url .= '/';
      }
      // Remove any leading '#' from channel.
      $channel_clean = ltrim($channel, '#');
      $channel_link = '<a href="' . $workspace_url . $channel_clean . '">#' . $channel_clean . '</a>';
    } else {
      $channel_link = $channel;
    }

    // Provide feedback.
    if ($result) {
      Drupal::messenger()->addMessage(Markup::create($this->t("Slack message posted to %channel: <pre>%preview</pre>", [
        '%channel' => $channel_link,
        '%preview' => $message,
      ])));
    } else {
      Drupal::messenger()->addMessage($this->t("Failed to post Slack message to %channel.", ['%channel' => $channel]), 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Optional: Add configuration settings for this handler.
    return [];
  }

}
