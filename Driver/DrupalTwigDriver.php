<?php

/*
 * This file is part of Mannequin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\Mannequin\Drupal\Driver;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Template\TwigExtension;
use Drupal\Core\Theme\ThemeManagerInterface;
use LastCall\Mannequin\Core\Cache\NullCacheItemPool;
use LastCall\Mannequin\Drupal\Drupal\MannequinDateFormatter;
use LastCall\Mannequin\Drupal\Drupal\MannequinExtensionDiscovery;
use LastCall\Mannequin\Drupal\Drupal\MannequinRenderer;
use LastCall\Mannequin\Drupal\Drupal\MannequinThemeManager;
use LastCall\Mannequin\Drupal\Drupal\MannequinUrlGenerator;
use LastCall\Mannequin\Twig\Driver\SimpleTwigDriver;
use Psr\Cache\CacheItemPoolInterface;

class DrupalTwigDriver extends SimpleTwigDriver
{
    private $booted;
    private $drupalRoot;
    private $cache;
    private $twigOptions;

    public function __construct(string $drupalRoot, array $twigOptions = [], CacheItemPoolInterface $cache = null)
    {
        if (!is_dir($drupalRoot)) {
            throw new \InvalidArgumentException(sprintf('Drupal root %s does not exist', $drupalRoot));
        }
        if (!file_exists(sprintf('%s/autoload.php', $drupalRoot))) {
            throw new \InvalidArgumentException(sprintf('Directory %s does not look like a Drupal installation', $drupalRoot));
        }
        $this->drupalRoot = $drupalRoot;
        $this->twigOptions = $twigOptions;
        $this->cache = $cache ?: new NullCacheItemPool();
    }

    public function getTwigRoot(): string
    {
        return $this->drupalRoot;
    }

    protected function createTwig(): \Twig_Environment
    {
        $this->boot();
        $twig = new \Twig_Environment($this->createLoader(), $this->twigOptions);
        $extension = new TwigExtension(
            $this->getRenderer(),
            $this->getGenerator(),
            $this->getThemeManager(),
            $this->getDateFormatter()
        );
        $twig->addExtension($extension);

        return $twig;
    }

    private function createLoader()
    {
        $this->boot();

        $loader = new \Twig_Loader_Filesystem([$this->drupalRoot], $this->drupalRoot);
        $discovery = new MannequinExtensionDiscovery($this->drupalRoot, $this->cache);
        foreach ($discovery->scan('module', false) as $key => $extension) {
            $dir = sprintf('%s/templates', $extension->getPath());
            if (is_dir(sprintf('%s/%s', $this->drupalRoot, $dir))) {
                $loader->addPath($key, $dir);
            }
        }
        foreach ($discovery->scan('theme', false) as $key => $extension) {
            $dir = sprintf('%s/templates', $extension->getPath());
            if (is_dir(sprintf('%s/%s', $this->drupalRoot, $dir))) {
                $loader->addPath($key, $dir);
            }
        }

        return $loader;
    }

    private function boot()
    {
        if (!$this->booted) {
            $this->booted = true;
            require sprintf('%s/autoload.php', $this->drupalRoot);
            require_once sprintf('%s/core/includes/bootstrap.inc', $this->drupalRoot);
        }
    }

    private function getRenderer(): RendererInterface
    {
        return new MannequinRenderer();
    }

    private function getGenerator(): UrlGeneratorInterface
    {
        return new MannequinUrlGenerator();
    }

    private function getThemeManager(): ThemeManagerInterface
    {
        return new MannequinThemeManager();
    }

    private function getDateFormatter(): DateFormatterInterface
    {
        return new MannequinDateFormatter();
    }
}
