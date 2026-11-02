<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../public/_lib/layout.php';

final class ToolPagesTest extends TestCase
{
    private const PUBLIC_DIR = __DIR__ . '/../public';

    public function test_every_tool_has_its_own_page(): void
    {
        foreach (array_keys(FERRAMENTAS) as $slug) {
            $this->assertFileExists(self::PUBLIC_DIR . "/$slug/index.php", "pagina de $slug");
        }
    }

    public function test_every_tool_page_is_in_the_sitemap(): void
    {
        $sitemap = (string) file_get_contents(self::PUBLIC_DIR . '/sitemap.xml');
        $this->assertStringContainsString('<loc>' . SITE_URL . '/</loc>', $sitemap);
        foreach (array_keys(FERRAMENTAS) as $slug) {
            $this->assertStringContainsString('<loc>' . SITE_URL . "/$slug/</loc>", $sitemap, $slug);
        }
    }

    public function test_every_tool_page_declares_its_own_canonical_path_and_title(): void
    {
        $titulos = [];
        foreach (array_keys(FERRAMENTAS) as $slug) {
            $fonte = (string) file_get_contents(self::PUBLIC_DIR . "/$slug/index.php");
            $this->assertStringContainsString("\$caminho = '/$slug/';", $fonte, $slug);
            $this->assertSame(1, preg_match("/'titulo' => '([^']+)'/", $fonte, $m), "titulo de $slug");
            $titulos[] = $m[1];
        }
        $this->assertSame($titulos, array_unique($titulos), 'titulos devem ser unicos');
    }

    public function test_version_helper_adds_mtime_to_asset_url(): void
    {
        $this->assertMatchesRegularExpression('#^/assets/app\.css\?v=\d+$#', v('assets/app.css'));
    }
}
