<?php
/**	op-asset-model:/Init.php
 *
 * @created   2025-10-02
 * @license   Apache-2.0
 * @package   op-unit-database-ci
 * @copyright (C) 2025 Tomoaki Nagahara
 */

/**	declare strict type
 *
 */
declare(strict_types=1);

/**	Namespace
 *
 */
namespace OP;

/**	Save the current directory.
 *
 */
$save_dir = getcwd();

/**	Changes the current directory.
 *
 */
chdir(__DIR__);

/**	Git hooks
 *
 */
include(__DIR__.'/.Init/GitHooks.php');

/**	Recovery the current directory.
 *
 */
chdir($save_dir);
