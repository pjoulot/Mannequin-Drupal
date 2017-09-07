<?php

/*
 * This file is part of Mannequin.
 *
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\Mannequin\Drupal\Drupal;

use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\Core\Template\TwigExtension;

class MannequinDrupalTwigExtension extends TwigExtension
{
    public function getFilters()
    {
        $filters = parent::getFilters();
        /** @var \Twig_SimpleFilter $filter */
        foreach ($filters as $i => $filter) {
            if ($filter instanceof \Twig_SimpleFilter) {
                switch ($filter->getName()) {
                    case 't':
                        $filters[$i] = new \Twig_SimpleFilter('t', [$this, 'trans'], ['is_safe' => ['html']]);
                        break;
                    case 'without':
                        $filters[$i] = new \Twig_SimpleFilter('without', [$this, 'without']);
                }
            }
        }

        return $filters;
    }

    public function trans($string, array $args = [], array $options = [])
    {
        $translation = new TranslationManager(new LanguageDefault([]));

        return new TranslatableMarkup($string, $args, $options, $translation);
    }

    public function without($element)
    {
        if ($element instanceof \ArrayAccess) {
            $filtered_element = clone $element;
        } else {
            $filtered_element = $element;
        }
        $args = func_get_args();
        unset($args[0]);
        foreach ($args as $arg) {
            if (isset($filtered_element[$arg])) {
                unset($filtered_element[$arg]);
            }
        }

        return $filtered_element;
    }
}
