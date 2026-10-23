# vermeuip.com.br

Site em português com IP do visitante, localização aproximada, provedor, IP reverso e quatro ferramentas: teste de portas, consulta DNS, whois (RDAP) e teste de velocidade. PHP 8 + HTML/CSS/JS puro, feito para hospedagem cPanel compartilhada.

Design: `docs/superpowers/specs/2026-10-23-vermeuip-design.md` · Plano: `docs/superpowers/plans/2026-10-23-vermeuip.md`

## Desenvolvimento

```powershell
winget install --id PHP.PHP.8.3 -e --source winget   # extensoes: curl intl mbstring openssl sockets fileinfo zip
php composer.phar install                            # instala public/vendor (fora do git)
.\scripts\fetch-dbip.ps1                             # baixa as bases DB-IP Lite (~130 MB, fora do git)
.\scripts\test.ps1                                   # PHPUnit
php -S localhost:8080 -t public                      # servidor local
```

Configuração em `public/_lib/settings.php` (`SPEED_ENGINE`, `TRUSTED_PROXIES`, `RATE_LIMIT_PER_MIN`).

## Deploy no cPanel

1. `.\scripts\build-package.ps1` gera `dist/vermeuip.zip` (só dependências de produção, bases DB-IP incluídas) e imprime o token do `check.php`.
2. No cPanel > Gerenciador de Arquivos: abra `public_html` (ou a pasta do domínio), envie o zip e extraia ali.
3. Abra `https://vermeuip.com.br/check.php?k=<token>` e confira PHP, extensões, saída TCP e HTTPS. **Depois apague `check.php` do servidor.**
4. DNS no Registro.br: aponte `vermeuip.com.br` e `www` para o IP do host (registro A) ou use os nameservers do host.
5. cPanel > Contas de e-mail: crie `contato@vermeuip.com.br`.
6. Teste de velocidade: `SPEED_ENGINE` = `'cloudflare'` (padrão, mede até a Cloudflare) ou `'librespeed'` (plano B, mede até o próprio host). Compare os dois no host real e deixe o melhor.

## Notas

- O teste de portas só testa o IP do próprio visitante. Atrás de CGNAT/firewall o resultado é "fechada ou filtrada". Se o host bloquear conexões de saída, a aba mostra erro claro.
- Atribuição obrigatória (CC BY 4.0): "IP Geolocation by DB-IP", já no rodapé. Atualize as bases uma vez por mês com `fetch-dbip.ps1`.
- `public/speedtest/` é o LibreSpeed (LGPL); `speedtest.js` tem um ajuste: caminho do worker `speedtest/speedtest_worker.js`.
- Servidor embutido do PHP (`php -S`) atende uma requisição por vez: o LibreSpeed local mede quase nada. Em produção (Apache/LiteSpeed) funciona normalmente.
- Microsoft Clarity carrega sempre (decisão do dono); IPs na página levam `data-clarity-mask`.
