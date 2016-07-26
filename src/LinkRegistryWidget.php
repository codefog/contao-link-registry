<?php

/**
 * link-registry extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2016 Codefog
 *
 * @author  Codefog <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace Codefog\LinkRegistryBundle;

use Contao\BackendTemplate;
use Contao\Image;
use Contao\Input;
use Contao\Widget;

class LinkRegistryWidget extends Widget
{
    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Validate the input and set the value
     */
    public function validate()
    {
        $types = [];

        // Prepare the types
        foreach ((array)$this->options as $option) {
            $types[] = $option['value'];
        }

        $value = [];

        // Include only those rows that have matching type
        foreach ($this->getPost($this->strName) as $type => $data) {
            if (in_array($type, $types, true)) {
                $value[$type] = [
                    'link'  => $data['link'],
                    'title' => $data['title'],
                ];
            }
        }

        $this->varValue = $value;
    }

    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        if (!is_array($this->varValue)) {
            $this->varValue = [];
        }

        $options = [];

        // Generate the options
        foreach ((array)$this->options as $option) {
            $linkId = sprintf('ctrl_%s_%s_%s_%s_link',
                $this->objDca->field,
                $this->objDca->id,
                $this->strId,
                $option['value']
            );

            $reference = $GLOBALS['TL_DCA'][$this->objDca->table]['fields'][$this->objDca->field]['reference'][$option['value']];

            $options[] = [
                'type'   => [
                    'label' => $option['label'],
                    'hint'  => is_array($reference) ? $reference[1] : '',
                ],
                'picker' => [
                    'tag' => $linkId,
                    'url' => sprintf(
                        'contao/page.php?do=%s&table=%s&field=%s&value=%s',
                        Input::get('do'),
                        $this->objDca->table,
                        $this->objDca->field,
                        str_replace(['{{link_url::', '}}',], '', $this->varValue[$option['value']]['link'])
                    ),
                ],
                'link'   => [
                    'id'    => $linkId,
                    'name'  => sprintf('%s[%s][link]', $this->strId, $option['value']),
                    'value' => $this->varValue[$option['value']]['link'],
                ],
                'title'  => [
                    'name'  => sprintf('%s[%s][title]', $this->strId, $option['value']),
                    'value' => $this->varValue[$option['value']]['title'],
                ],
            ];
        }

        $template          = new BackendTemplate('be_cfg_link_registry_widget');
        $template->options = $options;
        $template->field   = $this->objDca->field;

        $template->picker = [
            'id'    => $this->objDca->field,
            'title' => str_replace("'", "\\'", $GLOBALS['TL_LANG']['MOD']['page'][0]),
            'image' => Image::getHtml(
                'pickpage.gif',
                $GLOBALS['TL_LANG']['MSC']['pagepicker'],
                'style="vertical-align:top;cursor:pointer"'
            ),
        ];

        return $template->parse();
    }
}