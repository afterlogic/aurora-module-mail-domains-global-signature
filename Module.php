<?php

/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MailDomainsGlobalSignature;

use Aurora\Api;
use Aurora\System\Enums\UserRole;
use Aurora\System\Exceptions\ApiException;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractModule
{
	public function init()
	{
        $this->aRequireModules = ['MailDomains'];
		$this->aErrors = [];
    }

    /**
     * Api methods
     */

    public function GetGlobalSignatures($Search = '', $Offset = 0, $Limit = 0)
    {
        Api::checkUserRoleIsAtLeast(UserRole::SuperAdmin);

        $query = Models\GlobalSignature::query(); 
        
        if (!empty($Search)) {
            $query = $query->where('Name', 'Like', '%' . $Search . '%');
        }

        $count = $query->count();
        
        if ($Offset > 0) {
            $query = $query->offset($Offset);
        }

        if ($Limit > 0) {
            $query = $query->limit($Limit);
        }

        return [
            'Items' => $query->orderBy('Name', 'asc')->get()->toArray(),
            'Count' => $count
        ];
    }

    public function AddGlobalSignature($Name, $Signature)
    {
        $result = false;

        Api::checkUserRoleIsAtLeast(UserRole::SuperAdmin);

        if (Models\GlobalSignature::where('Name', $Name)->first()) {
            throw new ApiException(0);
        } else {
            $signature = new Models\GlobalSignature([
                'Name' => $Name,
                'Signature' => $Signature
            ]);

            $result = $signature->save();
        }

        return $result;
    }

    public function DeleteGlobalSignature($SignatureId)
    {
        $result = false;

        Api::checkUserRoleIsAtLeast(UserRole::SuperAdmin);

        $signature = Models\GlobalSignature::find($SignatureId);
        if ($signature) {
            $result = $signature->delete();
        }

        return $result;
    }

    public function UpdateGlobalSignature($SignatureId, $Name, $Signature)
    {
        $result = false;

        Api::checkUserRoleIsAtLeast(UserRole::SuperAdmin);

        $signature = Models\GlobalSignature::find($SignatureId);
        if ($signature) {
            if (Models\GlobalSignature::where('Id', '<>', $SignatureId)->where('Name', $Name)->first()) {
                throw new ApiException(0);
            }
            $signature->Name = $Name;
            $signature->Signature = $Signature;
            $result = $signature->save();
        }

        return $result;
    }

    public function GetSignature($SignatureId) 
    {
        Api::checkUserRoleIsAtLeast(UserRole::SuperAdmin);

        return Models\GlobalSignature::find($SignatureId);
    }
}
