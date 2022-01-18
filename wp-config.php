<?php
/**
 * As configurações básicas do WordPress
 *
 * O script de criação wp-config.php usa esse arquivo durante a instalação.
 * Você não precisa usar o site, você pode copiar este arquivo
 * para "wp-config.php" e preencher os valores.
 *
 * Este arquivo contém as seguintes configurações:
 *
 * * Configurações do MySQL
 * * Chaves secretas
 * * Prefixo do banco de dados
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Configurações do MySQL - Você pode pegar estas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define( 'DB_NAME', 'glotex' );

/** Usuário do banco de dados MySQL */
define( 'DB_USER', 'root' );

/** Senha do banco de dados MySQL */
define( 'DB_PASSWORD', '' );

/** Nome do host do MySQL */
define( 'DB_HOST', 'localhost' );

/** Charset do banco de dados a ser usado na criação das tabelas. */
define( 'DB_CHARSET', 'utf8mb4' );

/** O tipo de Collate do banco de dados. Não altere isso se tiver dúvidas. */
define( 'DB_COLLATE', '' );

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las
 * usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org
 * secret-key service}
 * Você pode alterá-las a qualquer momento para invalidar quaisquer
 * cookies existentes. Isto irá forçar todos os
 * usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Dh8io&[zBpQ1aiG/j@G(#U]XdOb-!^NH`f|3bEk,:GLw?$;Y0$+}[R%L4:?1@[Vl' );
define( 'SECURE_AUTH_KEY',  '&uf%xX>p[eD#NO-*7}FR<F;fu`^TJO#pe{-!Iw)1u-vO |=-*rN/P1[RsWv^Kx#=' );
define( 'LOGGED_IN_KEY',    '.h_qz(1HHA?TKmcb~Q7l_0O4~2ev2BY^*4HL#Gsqm:SnxkK|KK:<G?zS)=!7CBrx' );
define( 'NONCE_KEY',        'y1|&T)2^D$(<9.corz4pD={1GLdm*9!bD=6%7JI&dN%tG_qY{+8~itrdt(Z{{9*T' );
define( 'AUTH_SALT',        '{W31.[D]_~EY<2yx:XDs&N,p|B`lu{bmFwlL||6/>=>3G|[)zK!-XM%46xk@T?t`' );
define( 'SECURE_AUTH_SALT', ' J/e?>#)L 8_%6vmC,s_pd{I{+p7-~)@+<k`^.-M]^U-]ge*V6s?U~f+h+c!4h=L' );
define( 'LOGGED_IN_SALT',   'gZ$N|E,5N)*;ZB$xg6nC`ylU?U_b=#(5zWBISuBx,AGk,;SA#0kT;CRczli)]h#;' );
define( 'NONCE_SALT',       'rks0A>4VqDX(b*eba6CW&a~+t/gQL3~S/2_A:q$-C`)$b2ep4AiAZ04p&0kA|U8g' );

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der
 * um prefixo único para cada um. Somente números, letras e sublinhados!
 */
$table_prefix = 'wp_';

/**
 * Para desenvolvedores: Modo de debug do WordPress.
 *
 * Altere isto para true para ativar a exibição de avisos
 * durante o desenvolvimento. É altamente recomendável que os
 * desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 *
 * Para informações sobre outras constantes que podem ser utilizadas
 * para depuração, visite o Codex.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Configura as variáveis e arquivos do WordPress. */
require_once ABSPATH . 'wp-settings.php';
