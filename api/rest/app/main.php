<?php
use OpenApi\Annotations as OA;

require_once(__DIR__."/include.php");

/**
 * @OA\Info(
 *     version="1.0",
 *     title="Example for response examples value"
 * )
 */



/**
 * @OA\Schema(
 *  schema="Result",
 *  title="Sample schema for using references",
 * 	@OA\Property(
 * 		property="status",
 * 		type="string"
 * 	),
 * 	@OA\Property(
 * 		property="error",
 * 		type="string"
 * 	)
 * )
 */

handleRequest();
