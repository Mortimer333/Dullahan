<?php

declare(strict_types=1);

namespace Dullahan\Main\Service;

use EditorJS\EditorJS;
use EditorJS\EditorJSException;

/**
 * @TODO this a specific class for specific FE WYSIWYG tool - might be to specific :D
 */
class EditorJsService
{
    protected \HTMLPurifier_Config $sanitizer;
    protected \HTMLPurifier $purifier;
    // @TODO put in as parameter
    public const ALLOWED_TEXT_TAGS = 'i,b,a[href|target|rel],u[class],s[class],font[style],mark[style]';

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public function sanitize(array $data): array
    {
        $lists = $this->removeListFromBlock($data);

        $editor = new EditorJS(json_encode($data) ?: '', json_encode($this->getConfig()) ?: '');
        $blocks = $editor->getBlocks();

        $blocks = $this->fixList($lists, $blocks);

        return [
            'version' => $data['version'] ?? 0,
            'blocks' => $blocks,
            'time' => $data['time'] ?? 0,
        ];
    }

    /**
     * @param array<mixed> $data
     */
    public function parse(array $data): string
    {
        /** @var array<array{type: string, data: mixed, tunes: array<string, mixed>}> $blocks */
        $blocks = $data['blocks'] ?? $data;
        $html = '';
        foreach ($blocks as $block) {
            $html .= $this->parseBlock($block);
        }

        return $html;
    }

    /**
     * @param array{type: string, data: mixed, tunes: array<string, mixed>|null} $block
     */
    protected function parseBlock(array $block): string
    {
        $tag = $this->typeToTag($block);
        $html = '<' . $tag . $this->getAttributes($block) . '>';
        $html .= $this->retrieveBlockContent($block['type'], $tag, $block['data']);
        $html .= '</' . $tag . '>';

        return $html;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function retrieveBlockContent(string $type, string $tag, array $data): string
    {
        return match ($type) {
            'paragraph', 'header' => $data['text'],
            'button' => '<a target="_blank" rel="nofollow noindex noreferrer" href="' . $data['link'] . '">'
                . $data['text'] .
                '</a>',
            'list' => $this->buildListBlock($tag, $data['items']),
            'table' => $this->buildTableBlock($data['withHeadings'] ?? false, $data['content'] ?? []),
            default => '',
        };
    }

    /**
     * @param array<array<string>> $content
     */
    protected function buildTableBlock(bool $withHeadings, array $content): string
    {
        $html = '';
        foreach ($content as $i => $cells) {
            if ($withHeadings && 0 == $i) {
                $html .= '<div class="bm-table-row bm-table-header">';
            } else {
                $html .= '<div class="bm-table-row">';
            }
            foreach ($cells as $cell) {
                $html .= '<div class="bm-table-cell">';
                $html .= $cell;
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @param array<array{content: string, items: mixed}> $items
     */
    protected function buildListBlock(string $tag, array $items): string
    {
        $html = '';
        foreach ($items as $item) {
            $html .= '<li>';
            $html .= $item['content'];
            if (\count($item['items'] ?? []) > 0) {
                $html .= '<' . $tag . '>';
                $html .= $this->buildListBlock($tag, $item['items']);
                $html .= '</' . $tag . '>';
            }
            $html .= '</li>';
        }

        return $html;
    }

    /**
     * @param array{type: string, data: mixed, tunes: array<string, mixed>|null} $block
     */
    protected function getAttributes(array $block): string
    {
        $attributes = $this->getTunes($block['tunes'] ?? []);
        $func = match ($block['type']) {
            'table' => function () use (&$attributes): void {
                if (!isset($attributes['class'])) {
                    $attributes['class'] = [];
                }
                $attributes['class']['bm-table'] = true;
            },
            default => fn () => null,
        };
        $func();

        $html = '';
        $attrImploders = [
            'style' => function (array $attributes): string {
                $style = 'style="';
                foreach ($attributes as $name => $attribute) {
                    $style .= $name . ':' . $attribute . ';';
                }

                return $style . '"';
            },
            'class' => function (array $classes): string {
                return 'class="' . implode(' ', array_keys($classes)) . '"';
            },
        ];

        foreach ($attributes as $name => $attribute) {
            $html .= ' ' . trim(($attrImploders[$name] ?? throw new \Exception('Tune not found', 400))($attribute));
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $tunes
     *
     * @return array<string, mixed>
     */
    protected function getTunes(array $tunes): array
    {
        $attributes = [];
        $tunesResolver = [
            'align' => function (array $tune) use (&$attributes): void {
                if (!isset($attributes['style'])) {
                    $attributes['style'] = [];
                }
                $attributes['style']['text-align'] = $tune['alignment'];
            },
        ];

        foreach ($tunes as $name => $tune) {
            ($tunesResolver[$name] ?? throw new \Exception('Tune not found', 400))($tune);
        }

        return $attributes;
    }

    /**
     * @param array{type: string, data: mixed, tunes: array<string, mixed>|null} $block
     */
    protected function typeToTag(array $block): string
    {
        $types = [
            'paragraph' => fn () => 'p',
            'header' => fn (array $block) => 'h' . ($block['data']['level'] ?? 1),
            'list' => fn (array $block) => 'unordered' === $block['data']['style'] ? 'ul' : 'ol',
            'table' => fn () => 'div',
            'button' => fn () => 'button',
        ];

        return ($types[$block['type']] ?? throw new \Exception('Editor type not recognized', 400))($block);
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    protected function removeListFromBlock(array &$data): array
    {
        $lists = [];
        foreach ($data['blocks'] as $i => $block) {
            if ('list' == $block['type']) {
                $lists[] = [
                    'pos' => $i,
                    'list' => $block,
                ];
            }
        }

        $reversed = array_reverse($lists);
        foreach ($reversed as $list) {
            array_splice($data['blocks'], $list['pos'], 1);
        }

        return $lists;
    }

    /**
     * @param array<mixed> $lists
     * @param array<mixed> $blocks
     *
     * @return array<mixed>
     *
     * @throws EditorJSException
     */
    protected function fixList(array $lists, array $blocks): array
    {
        foreach ($lists as $listCon) {
            $list = $listCon['list']['data'];
            if (!isset($list['style']) || ('unordered' != $list['style'] && 'ordered' != $list['style'])) {
                throw new EditorJSException('Nested list can only be ordered or unordered', 400);
            }
            unset($listCon['list']['id']);

            foreach ($list['items'] as $i => $item) {
                $list['items'][$i] = $this->sanitizeListItem($item);
            }

            $listCon['list']['data'] = $list;

            array_splice($blocks, $listCon['pos'], 0, [$listCon['list']]);
        }

        return $blocks;
    }

    /**
     * @param array<mixed> $item
     *
     * @return array<mixed>
     */
    protected function sanitizeListItem(array $item): array
    {
        $item['content'] = $this->getPurifier(self::ALLOWED_TEXT_TAGS)->purify($item['content']);
        if (isset($item['items'])) {
            foreach ($item['items'] as $i => $subItem) {
                $item['items'][$i] = $this->sanitizeListItem($subItem);
            }
        }

        return $item;
    }

    /**
     * @return array<mixed>
     */
    protected function getConfig(): array
    {
        return [
            'tools' => [
                'paragraph' => [
                    'text' => [
                        'type' => 'string',
                        'allowedTags' => self::ALLOWED_TEXT_TAGS,
                        'required' => true,
                    ],
                ],
                'header' => [
                    'text' => 'string',
                    'level' => [
                        'type' => 'int',
                        'canBeOnly' => [1, 2, 3, 4, 5],
                    ],
                ],
                'table' => [
                    'withHeadings' => 'bool',
                    'content' => [
                        'type' => 'array',
                        'data' => [
                            '-' => [
                                'type' => 'array',
                                'data' => [
                                    '-' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'button' => [
                    'link' => 'string',
                    'text' => 'string',
                ],
            ],
        ];
    }

    private function getPurifier(string $allowedTags): \HTMLPurifier
    {
        if (isset($this->purifier)) {
            return $this->purifier;
        }

        $sanitizer = $this->getDefaultPurifier();

        $sanitizer->set('HTML.Allowed', $allowedTags);

        /*
         * Define custom HTML Definition for mark tool
         */
        if ($def = $sanitizer->maybeGetRawHTMLDefinition()) {
            $def->addElement('mark', 'Inline', 'Inline', 'Common');
        }

        $this->purifier = new \HTMLPurifier($sanitizer);

        return $this->purifier;
    }

    private function getDefaultPurifier(): \HTMLPurifier_Config
    {
        if (isset($this->sanitizer)) {
            return $this->sanitizer;
        }

        $sanitizer = \HTMLPurifier_Config::createDefault();

        $sanitizer->set('HTML.TargetBlank', true);
        $sanitizer->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $sanitizer->set('AutoFormat.RemoveEmpty', true);
        $sanitizer->set('HTML.DefinitionID', 'html5-definitions');

        $cacheDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'purifier';
        if (!is_dir($cacheDirectory)) {
            mkdir($cacheDirectory, 0777, true);
        }

        $sanitizer->set('Cache.SerializerPath', $cacheDirectory);

        $this->sanitizer = $sanitizer;

        return $sanitizer;
    }
}
