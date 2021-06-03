<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en « wp-config.php » et remplir les
 * valeurs.
 *
 * Ce fichier contient les réglages de configuration suivants :
 *
 * Réglages MySQL
 * Préfixe de table
 * Clés secrètes
 * Langue utilisée
 * ABSPATH
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', '2wng' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'root' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', '' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/**
 * Type de collation de la base de données.
 * N’y touchez que si vous savez ce que vous faites.
 */
define( 'DB_COLLATE', '' );

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'BSfVdXrtQs4;X?H5w/Oxec|YjGil6;,]Ce#jnIif!Ng;*Eq6F1S>&cCb4QP9e|0u' );
define( 'SECURE_AUTH_KEY',  '< o/$<Lduxk#<=+AeH_shidZmt}IlM{IwX_$]ul0~`%02T%(P(Jup/tsS,B(E}TV' );
define( 'LOGGED_IN_KEY',    'RT}$z2q*K=;{8XMU:RH)RZjnGceW}%q5_(+1@X,Ks1l;-%9Fa]Y7s(#j-fTF}& ;' );
define( 'NONCE_KEY',        '=?C_rG}A+NMOQi3a}+}92oNl^J1OhVR:%NT$@~j]WK?_T)f]Ie8m1XbrLd$jBb21' );
define( 'AUTH_SALT',        'rsM?ZfM9[7dLV?)5MUVd2fbWcL}@ _8bOmhQ_P@Mqb `)W#rhg,}Uwd5bfI3$]r3' );
define( 'SECURE_AUTH_SALT', 'pI9oo =tFB#M)4=2p9uu@LnWAqrF)w+v6Ns2+Zt!^D|PSa5;}>H]m5cHWX]$Xd<W' );
define( 'LOGGED_IN_SALT',   '6m7Qp<=)a2Y9r3!vZAZRL^K=s0|;?l]k0hRbYbulu~8 -$0[$4ax4kbb6Pxy,tw.' );
define( 'NONCE_SALT',       'Njm`3?i4R/VA/]};FH|ghfV/=@!EM0gU%s}GJ4TH6^[GcV=nupUe=$(V(#Rb_~-L' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( ! defined( 'ABSPATH' ) )
  define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once( ABSPATH . 'wp-settings.php' );
