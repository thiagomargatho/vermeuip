<?php

declare(strict_types=1);

/*
 * Extrai o logo da prancha de identidade visual (docs/marca/identidade-visual.webp) para public/assets/marca/.
 * Nao redesenha nada: so recorta a arte aprovada e tira o fundo (transparente).
 *
 * Uso: php scripts/gerar-logo.php
 * Requer a extensao GD (com WebP).
 *
 * Saidas (PNG com transparencia):
 *   logo.png          logo completo (marca + VerMeuIP), para fundo CLARO
 *   logo-escuro.png   logo completo, para fundo ESCURO (variacao oficial "fundo escuro")
 *   marca.png         so o simbolo "IP" (base do favicon)
 *   favicon-32.png, favicon-192.png, apple-touch-icon.png, og.png
 */

$raiz = dirname(__DIR__);
$fonte = $raiz . '/docs/marca/identidade-visual.webp';
$saida = $raiz . '/public/assets/marca';
@mkdir($saida, 0775, true);

$prancha = imagecreatefromwebp($fonte);
if ($prancha === false) {
    fwrite(STDERR, "Nao abriu $fonte\n");
    exit(1);
}

function rgb(GdImage $im, int $x, int $y): array
{
    $c = imagecolorat($im, $x, $y);
    return [($c >> 16) & 255, ($c >> 8) & 255, $c & 255];
}

/** Copia uma regiao para uma imagem nova (truecolor). */
function recorte(GdImage $im, int $x, int $y, int $w, int $h): GdImage
{
    $novo = imagecreatetruecolor($w, $h);
    imagecopy($novo, $im, 0, 0, $x, $y, $w, $h);
    return $novo;
}

/** Fundo claro -> transparente ("color to alpha" contra o branco da prancha). */
function fundoClaroParaAlpha(GdImage $im, array $fundo = [254, 254, 254]): GdImage
{
    $w = imagesx($im);
    $h = imagesy($im);
    $out = imagecreatetruecolor($w, $h);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            [$r, $g, $b] = rgb($im, $x, $y);
            $a = max(($fundo[0] - $r) / $fundo[0], ($fundo[1] - $g) / $fundo[1], ($fundo[2] - $b) / $fundo[2]);
            $a = max(0.0, min(1.0, $a));
            if ($a < 0.02) {
                imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, 255, 255, 255, 127));
                continue;
            }
            $r2 = (int) round(max(0, min(255, ($r - $fundo[0] * (1 - $a)) / $a)));
            $g2 = (int) round(max(0, min(255, ($g - $fundo[1] * (1 - $a)) / $a)));
            $b2 = (int) round(max(0, min(255, ($b - $fundo[2] * (1 - $a)) / $a)));
            imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r2, $g2, $b2, (int) round(127 - 127 * $a)));
        }
    }
    return $out;
}

/** Fundo escuro (cor unica) -> transparente, com borda suave. */
function fundoEscuroParaAlpha(GdImage $im, array $fundo, float $suave = 60.0): GdImage
{
    $w = imagesx($im);
    $h = imagesy($im);
    $out = imagecreatetruecolor($w, $h);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            [$r, $g, $b] = rgb($im, $x, $y);
            $d = sqrt(($r - $fundo[0]) ** 2 + ($g - $fundo[1]) ** 2 + ($b - $fundo[2]) ** 2);
            $a = max(0.0, min(1.0, ($d - 6) / $suave));
            imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, (int) round(127 - 127 * $a)));
        }
    }
    return $out;
}

/** Remove margens totalmente transparentes. */
function aparar(GdImage $im, int $margem = 0): GdImage
{
    $w = imagesx($im);
    $h = imagesy($im);
    $x0 = $w; $y0 = $h; $x1 = -1; $y1 = -1;
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $alpha = (imagecolorat($im, $x, $y) >> 24) & 127;
            if ($alpha < 100) {
                $x0 = min($x0, $x); $x1 = max($x1, $x);
                $y0 = min($y0, $y); $y1 = max($y1, $y);
            }
        }
    }
    $x0 = max(0, $x0 - $margem); $y0 = max(0, $y0 - $margem);
    $x1 = min($w - 1, $x1 + $margem); $y1 = min($h - 1, $y1 + $margem);
    $out = imagecreatetruecolor($x1 - $x0 + 1, $y1 - $y0 + 1);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
    imagecopy($out, $im, 0, 0, $x0, $y0, $x1 - $x0 + 1, $y1 - $y0 + 1);
    return $out;
}

function salvar(GdImage $im, string $arquivo): void
{
    imagealphablending($im, false);
    imagesavealpha($im, true);
    imagepng($im, $arquivo, 9);
    printf("%-22s %4dx%-4d %6.1f KB\n", basename($arquivo), imagesx($im), imagesy($im), filesize($arquivo) / 1024);
}

function redimensionar(GdImage $im, int $largura, ?int $altura = null): GdImage
{
    $altura ??= (int) round(imagesy($im) * $largura / imagesx($im));
    $out = imagecreatetruecolor($largura, $altura);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
    imagecopyresampled($out, $im, 0, 0, 0, 0, $largura, $altura, imagesx($im), imagesy($im));
    return $out;
}

// 1) logo completo, versao colorida para fundo claro (area da prancha: x 20..720, y 90..580)
$completo = aparar(fundoClaroParaAlpha(recorte($prancha, 20, 90, 700, 490)), 2);
salvar($completo, "$saida/logo.png");

// 2) so o simbolo (acima do texto "VerMeuIP"): x 20..720, y 90..420
$marca = aparar(fundoClaroParaAlpha(recorte($prancha, 20, 90, 700, 330)), 2);
salvar($marca, "$saida/marca.png");

// 3) variacao para fundo escuro: recolore a PROPRIA arte em alta resolucao (nao redesenha nada).
//    O azul-marinho vira branco; o gradiente (amarelo/laranja/vermelho/rosa) fica igual.
//    Pixels que misturam marinho e cor (sombra do "1") trocam so a parte marinha por branco.
function marinhoParaBranco(GdImage $im): GdImage
{
    $w = imagesx($im);
    $h = imagesy($im);
    $out = imagecreatetruecolor($w, $h);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    $navy = [11, 31, 68];
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $c = imagecolorat($im, $x, $y);
            $a = ($c >> 24) & 127;
            [$r, $g, $b] = [($c >> 16) & 255, ($c >> 8) & 255, $c & 255];
            // peso do marinho: o vermelho vai de 11 (marinho) a ~240 (cores quentes)
            $peso = max(0.0, min(1.0, 1 - ($r - $navy[0]) / (240 - $navy[0])));
            $r2 = (int) round(min(255, max(0, $r + $peso * (255 - $navy[0]))));
            $g2 = (int) round(min(255, max(0, $g + $peso * (255 - $navy[1]))));
            $b2 = (int) round(min(255, max(0, $b + $peso * (255 - $navy[2]))));
            imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r2, $g2, $b2, $a));
        }
    }
    return $out;
}
$escuro = marinhoParaBranco($completo);
salvar($escuro, "$saida/logo-escuro.png");

// 4) favicons a partir do simbolo
$quadrado = function (GdImage $simbolo, int $lado, ?array $fundo = null, float $folga = 0.12): GdImage {
    $out = imagecreatetruecolor($lado, $lado);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagefill($out, 0, 0, $fundo ? imagecolorallocatealpha($out, $fundo[0], $fundo[1], $fundo[2], 0) : imagecolorallocatealpha($out, 0, 0, 0, 127));
    $miolo = (int) round($lado * (1 - 2 * $folga));
    $prop = imagesx($simbolo) / imagesy($simbolo);
    $w = $prop >= 1 ? $miolo : (int) round($miolo * $prop);
    $h = $prop >= 1 ? (int) round($miolo / $prop) : $miolo;
    imagealphablending($out, true);
    imagecopyresampled($out, $simbolo, (int) (($lado - $w) / 2), (int) (($lado - $h) / 2), 0, 0, $w, $h, imagesx($simbolo), imagesy($simbolo));
    imagealphablending($out, false);
    return $out;
};
salvar($quadrado($marca, 32, null, 0.04), "$saida/favicon-32.png");
salvar($quadrado($marca, 192, null, 0.06), "$saida/favicon-192.png");
salvar($quadrado($marca, 180, [255, 255, 255], 0.14), "$saida/apple-touch-icon.png");

// 5) imagem de compartilhamento 1200x630: logo completo centralizado sobre branco, com uma linha de apoio
$og = imagecreatetruecolor(1200, 630);
imagefill($og, 0, 0, imagecolorallocate($og, 255, 255, 255));
$larg = 470;
$logoOg = redimensionar($completo, $larg);
imagealphablending($og, true);
imagecopy($og, $logoOg, (int) ((1200 - $larg) / 2), 48, 0, 0, imagesx($logoOg), imagesy($logoOg));
$fonte = 'C:/Windows/Fonts/segoeui.ttf';
if (is_file($fonte)) {
    $navy = imagecolorallocate($og, 11, 31, 68);
    $texto = 'Qual é o meu IP? Teste de velocidade com um clique.';
    $caixa = imagettfbbox(34, 0, $fonte, $texto);
    imagettftext($og, 34, 0, (int) ((1200 - ($caixa[2] - $caixa[0])) / 2), 585, $navy, $fonte, $texto);
}
imagepng($og, "$saida/og.png", 9);
printf("%-22s %4dx%-4d %6.1f KB\n", 'og.png', 1200, 630, filesize("$saida/og.png") / 1024);
