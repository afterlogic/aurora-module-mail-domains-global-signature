<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MailDomainsGlobalSignature\Models;

/**
 * Aurora\Modules\MailDomainsGlobalSignature\Models\GlobalSignature
 *
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 * @property integer $Id
 * @property string $Name
 * @property mixed $Signature
 * @property-read mixed $entity_id
 * @method static int count(string $columns = '*')
 * @method static \Illuminate\Database\Eloquent\Builder|\Aurora\Modules\MailDomainsGlobalSignature\Models\GlobalSignature find(int|string $id, array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|\Aurora\Modules\MailDomainsGlobalSignature\Models\GlobalSignature findOrFail(int|string $id, mixed $id, Closure|array|string $columns = ['*'], Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aurora\Modules\MailDomainsGlobalSignature\Models\GlobalSignature first(array|string $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|\Aurora\Modules\MailDomainsGlobalSignature\Models\GlobalSignature firstWhere(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSignature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSignature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSignature query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Aurora\Modules\MailDomainsGlobalSignature\Models\GlobalSignature where(Closure|string|array|\Illuminate\Database\Query\Expression $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSignature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Aurora\Modules\MailDomainsGlobalSignature\Models\GlobalSignature whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSignature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GlobalSignature whereSignature($value)
 * @mixin \Eloquent
 */
class GlobalSignature extends \Aurora\System\Classes\Model
{
    protected $table = 'mail_domains_global_signature';

    protected $moduleName = 'MailDomainsGlobalSignature';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Id',
        'Name',
        'Signature'
    ];
}
