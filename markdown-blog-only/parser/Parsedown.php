<?php

if (!class_exists('Parsedown')) {
    class Parsedown {
        /**
         * Minimal Markdown to HTML conversion.
         *
         * @param string $text
         * @return string
         */
        public function text($text) {
            $text = (string) $text;
            $text = str_replace(["\r\n", "\r"], "\n", $text);
            $lines = explode("\n", $text);

            $html = '';
            $inUl = false;
            $inOl = false;
            $inCode = false;

            foreach ($lines as $line) {
                if (preg_match('/^```/', $line)) {
                    if ($inUl) {
                        $html .= "</ul>\n";
                        $inUl = false;
                    }
                    if ($inOl) {
                        $html .= "</ol>\n";
                        $inOl = false;
                    }
                    if (!$inCode) {
                        $html .= "<pre><code>";
                        $inCode = true;
                    } else {
                        $html .= "</code></pre>\n";
                        $inCode = false;
                    }
                    continue;
                }

                if ($inCode) {
                    $html .= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . "\n";
                    continue;
                }

                if (trim($line) === '') {
                    if ($inUl) {
                        $html .= "</ul>\n";
                        $inUl = false;
                    }
                    if ($inOl) {
                        $html .= "</ol>\n";
                        $inOl = false;
                    }
                    continue;
                }

                if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $m)) {
                    $level = strlen($m[1]);
                    $html .= '<h' . $level . '>' . $this->inline($m[2]) . '</h' . $level . ">\n";
                    continue;
                }

                if (preg_match('/^\s*[-*+]\s+(.+)$/', $line, $m)) {
                    if (!$inUl) {
                        if ($inOl) {
                            $html .= "</ol>\n";
                            $inOl = false;
                        }
                        $html .= "<ul>\n";
                        $inUl = true;
                    }
                    $html .= '<li>' . $this->inline($m[1]) . "</li>\n";
                    continue;
                }

                if (preg_match('/^\s*\d+\.\s+(.+)$/', $line, $m)) {
                    if (!$inOl) {
                        if ($inUl) {
                            $html .= "</ul>\n";
                            $inUl = false;
                        }
                        $html .= "<ol>\n";
                        $inOl = true;
                    }
                    $html .= '<li>' . $this->inline($m[1]) . "</li>\n";
                    continue;
                }

                $html .= '<p>' . $this->inline($line) . "</p>\n";
            }

            if ($inUl) {
                $html .= "</ul>\n";
            }
            if ($inOl) {
                $html .= "</ol>\n";
            }
            if ($inCode) {
                $html .= "</code></pre>\n";
            }

            return $html;
        }

        /**
         * Parse inline markdown.
         *
         * @param string $text
         * @return string
         */
        private function inline($text) {
            $text = htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');

            $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
            $text = preg_replace('/__(.*?)__/', '<strong>$1</strong>', $text);
            $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
            $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);
            $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
            $text = preg_replace_callback('/\[(.*?)\]\((https?:\/\/[^\s)]+)\)/', function ($m) {
                return '<a href="' . $m[2] . '">' . $m[1] . '</a>';
            }, $text);

            return $text;
        }
    }
}
