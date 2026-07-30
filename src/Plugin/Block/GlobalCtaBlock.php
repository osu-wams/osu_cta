<?php

declare(strict_types=1);

namespace Drupal\osu_cta\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'GlobalCtaBlock' block.
 */
#[Block(
  id: 'global_cta_block',
  admin_label: new TranslatableMarkup('Global Call to Action'),
  category: new TranslatableMarkup('OSU'),
)]
class GlobalCtaBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a OSU Global CTA Block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The Configuration Factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly ConfigFactory $config_factory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritDoc}
   */
  public function build(): array {
    $osuCtaData = $this->config_factory->get('osu_cta.settings')->get('global_cta') ?? [];
    $osuCta = array_map(static function ($cta) {
      if (preg_match('/^(https?:|mailto:|tel:)/', $cta['uri'])) {
        $url = Url::fromUri(
          $cta['uri'],
          $cta['uri']['options'] ?? []
        );
      }
      else {
        $url = Url::fromUserInput($cta['uri']);
      }

      return [
        'title' => $cta['title'],
        'link' => $url,
        'icon' => $cta['icon'],
      ];
    }, $osuCtaData);

    return [
      '#type' => 'component',
      '#component' => 'osu_cta:global-cta',
      '#props' => [
        'items' => $osuCta,
      ],
      '#cache' => [
        'tags' => ['config:osu_cta.settings'],
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
    );
  }

}
