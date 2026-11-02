# Duas lojas com mesmo código — diagnóstico seguro de 404 da logo e 403 do painel

Este procedimento foi preparado para instalações independentes do e-commerce, por exemplo, **SABA** e **Lojinha**. Não execute comandos de banco ou exclusão antes de conferir em qual pasta está.

## 1. Em cada instalação, rode o diagnóstico (somente leitura)

```bash
cd /CAMINHO/DO/SABA
php artisan loja:diagnosticar

cd /CAMINHO/DA/LOJINHA
php artisan loja:diagnosticar
```

Compare o caminho do projeto, `APP_URL`, nome do banco, cookie de sessão, destino do `public/storage` e quantidade de usuários no grupo Administrador.

Cada domínio precisa ter sua própria configuração de ambiente. Especialmente:

```dotenv
# INSTALAÇÃO SABA — valores ilustrativos
APP_NAME="Loja Saba"
APP_URL=https://DOMINIO-DO-SABA
DB_DATABASE=banco_saba
SESSION_COOKIE=saba_session
SESSION_DOMAIN=
```

```dotenv
# INSTALAÇÃO LOJINHA — valores ilustrativos
APP_NAME="Lojinha"
APP_URL=https://DOMINIO-DA-LOJINHA
DB_DATABASE=banco_lojinha
SESSION_COOKIE=lojinha_session
SESSION_DOMAIN=
```

**Não copie esses valores sem adaptá-los aos dados reais.** Banco de dados e sessão devem estar isolados quando as lojas forem independentes. Não exponha `APP_KEY`, senhas e tokens em mensagens de suporte.

**Não execute `php artisan key:generate` na instalação existente.** O APP_KEY atual pode ser necessário para decifrar credenciais de gateways e outros dados armazenados.

## 2. Resolver 404 da logo

O upload usa o disco `public`: arquivo físico em `storage/app/public/store/branding` e URL pública em `/storage/store/branding/...`.

Na pasta **da instalação com erro**, rode:

```bash
ls -ld public/storage
readlink public/storage
ls -lah storage/app/public/store/branding/
```

Se `public/storage` for um **link simbólico** cujo destino é o projeto antigo (ou está quebrado), faça backup e recrie **apenas o link**:

```bash
# Confira o resultado antes de qualquer alteração:
[ -L public/storage ] && readlink public/storage

# Somente se for link simbólico errado ou quebrado:
[ -L public/storage ] && unlink public/storage
php artisan storage:link
php artisan optimize:clear
```

**Não remova** `public/storage` se for uma pasta real: contenha-se e verifique o conteúdo antes de movimentar qualquer dado. **Nunca apague** `storage/app/public`; ali ficam fotos de produtos, banners e marcas.

Se `public/storage` aponta corretamente mas a URL ainda responde 404, confira o *document root* do domínio (deve ser a pasta `public` desta instalação), permissão de leitura dos arquivos e suporte a links simbólicos do Apache/Nginx/cPanel.

A atualização de código inclui URLs relativas ao domínio atual para a logo. Isso evita que a URL publicada seja gerada com um `APP_URL` de outra loja, mas **não substitui a configuração correta do servidor**.

## 3. Resolver 403 do `/admin`

1. Faça login em janela privativa no domínio correto. Pode haver uma sessão antiga ou uma conta de cliente logada no guard web.
2. Confirme, com `php artisan loja:diagnosticar`, se o usuário possui grupo e se as duas lojas não usam por engano o mesmo banco.
3. Para **identificar a conta sem grupo** (na instalação correta), consulte:

```bash
php artisan tinker
```

```php
\App\Models\User::with('adminGroup')->get(['id', 'name', 'email', 'admin_group_id'])
    ->each(fn ($u) => print($u->id.' | '.$u->email.' | '.($u->adminGroup?->slug ?? 'SEM GRUPO').PHP_EOL));
```

4. Se o login administrativo legítimo estiver sem grupo, **após confirmar que você está no banco correto e fazer backup**, atribua o grupo Administrador apenas àquela conta:

```php
$user = \App\Models\User::where('email', 'EMAIL_EXATO_DO_ADMIN')->firstOrFail();
$group = \App\Domain\Access\Models\AdminGroup::where('slug', 'administrador')->firstOrFail();
$user->adminGroup()->associate($group);
$user->save();
```

Isso não altera os demais usuários. **Não edite `canAccessPanel()` para retornar `true`**, pois isso elimina uma barreira de segurança.

Se o usuário já está no grupo correto, confira `APP_URL`, `SESSION_COOKIE`, `SESSION_DOMAIN`, cache de configuração e se os dois domínios compartilham inadvertidamente o mesmo banco ou a mesma sessão. Execute `php artisan optimize:clear` na pasta correta e tente novo login. Se continuar 403, guarde a URL exata da página e a mensagem de log (sem dados sensíveis).

## 4. Cuidados com `cp -r`

Copiar o projeto inteiro também pode copiar `.env`, sessões, caches, logs, arquivos enviados, chaves de criptografia e um link de `public/storage` apontando para a instalação antiga. O ideal é distribuir **somente o código**, depois configurar **cada instalação** com seu próprio `.env` e armazenamento; preservar as chaves existentes e os dados de cada loja.

Não execute `migrate:fresh`, `db:wipe`, `key:generate` ou exclusões recursivas para tentar corrigir os problemas descritos.
