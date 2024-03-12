<?php

namespace Drupal\occuspace\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Http\ClientFactory;

/**
 * Provides a block to display API data.
 *
 * @Block(
 *   id = "Occuspace Block",
 *   admin_label = @Translation("Occuspace Block"),
 * )
 */
class OccuspaceBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The HTTP client factory.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientFactory $http_client_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->httpClientFactory = $http_client_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client_factory')
    );
  }

/**
 * {@inheritdoc}
 */
public function build() {
    $config = \Drupal::config('occuspace.settings');
    $apiToken = $config->get('api_token');
    \Drupal::logger('occuspace')->info("API token is {$apiToken}");
    $client = $this->httpClientFactory->fromOptions([
      'headers' => [
        'Authorization' => "Bearer {$apiToken}",
      ],
    ]);
    try {
      $response = $client->request('GET', 'https://api.occuspace.io/v1/location/3118/now');
      $responseData = json_decode($response->getBody(), true);

      // Check if the response contains the required data
      if (isset($responseData['data'])) {
        $data = $responseData['data'];
        $items = [
          'Name: ' . $data['name'],
          'Count: ' . $data['count'],
          'Percentage: ' . ($data['percentage'] * 100) . '%',
          'Timestamp: ' . $data['timestamp'],
          'Is Active: ' . ($data['isActive'] ? 'Yes' : 'No'),
        ];

        // Handling child counts
        if (!empty($data['childCounts'])) {
          foreach ($data['childCounts'] as $child) {
            $items[] = '---';
            $items[] = 'Child Name: ' . $child['name'];
            $items[] = 'Child Count: ' . $child['count'];
            $items[] = 'Child Percentage: ' . ($child['percentage'] * 100) . '%';
            $items[] = 'Child Is Active: ' . ($child['isActive'] ? 'Yes' : 'No');
          }
        }

        return [
          '#theme' => 'item_list',
          '#items' => $items,
          '#title' => $this->t('Location Data'),
        ];
      } 
      else {
        return ['#markup' => $this->t('No data found in API response.')];
      }
    } 
    catch (\Exception $e) {
      return [
        '#markup' => $this->t('Failed to fetch API data: @message', ['@message' => $e->getMessage()]),
      ];
    }
  }

  /**
   * @return int
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
