<?php
/**	op-asset-model:/Init.php
 *
 * @created   2025-10-02
 * @version   1.0
 * @package   op-asset-model
 * @author    Tomoaki Nagahara
 * @copyright Tomoaki Nagahara All rights reserved.
 */

/**	declare
 *
 */
declare(strict_types=1);

/**	namespace
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
