<?php

/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MailDomainsGlobalSignature;

use Aurora\Api;
use Aurora\Modules\Core\Models\User;
use Aurora\Modules\Mail\Models\MailAccount;
use Aurora\Modules\MailDomains\Models\Domain;
use Aurora\Modules\MailDomainsGlobalSignature\Models\GlobalSignature;
use Aurora\System\Enums\UserRole;
use Aurora\System\Exceptions\ApiException;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractModule
{
	public function init()
	{
        $this->aRequireModules = ['MailDomains'];
		$this->aErrors = [];
        $this->subscribeEvent('MailDomains::Domain::ToResponseArray', [$this, 'onDomainToResponseArray']);
		$this->subscribeEvent('Mail::Account::ToResponseArray', [$this, 'onMailAccountToResponseArray']);
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

        return $signature->Id;
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

    public function AddGlobalSignatureToDomain($DomainId, $SignatureId)
    {   
        $result = false;

        Api::checkUserRoleIsAtLeast(UserRole::SuperAdmin);

        $domain = Domain::find($DomainId);

        if ($domain) {

            $signature = GlobalSignature::find($SignatureId);
            if ($signature) {
                $domain->setExtendedProp(self::GetName() . '::SignatureId', $SignatureId);
                $result = $domain->save();
            }

        }

        return $result;
    }

    public function RemoveGlobalSignatureFromDomain($DomainId)
    {
        $result = false;

        Api::checkUserRoleIsAtLeast(UserRole::SuperAdmin);

        $domain = Domain::find($DomainId);

        if ($domain) {

            $domain->unsetExtendedProp(self::GetName() . '::SignatureId');
            $result = $domain->save();

        }

        return $result;
    }

    public function UpdateGlobalSignatureUserData($UserId, $UseGlobalSignature, $AllowCreatingIdentities, $Name, $Position, $Phone, $Email, $Optional)
    {
        $result = false;

        Api::checkUserRoleIsAtLeast(UserRole::SuperAdmin);
        Api::CheckAccess($UserId);

        $user = User::find($UserId);

        if ($user) {

            $user->setExtendedProp(self::GetName() . '::UseGlobalSignature', $UseGlobalSignature);
            $user->setExtendedProp(self::GetName() . '::AllowCreatingIdentities', $AllowCreatingIdentities);
            $user->setExtendedProp(self::GetName() . '::Name', $Name);
            $user->setExtendedProp(self::GetName() . '::Position', $Position);
            $user->setExtendedProp(self::GetName() . '::Phone', $Phone);
            $user->setExtendedProp(self::GetName() . '::Email', $Email);
            $user->setExtendedProp(self::GetName() . '::Optional', $Optional);
            $result = $user->save();

        }

        return $result;

    }

    public function onDomainToResponseArray($aArgs, &$mResult)
	{
		if (is_array($aArgs) && isset($aArgs['Domain']) && $aArgs['Domain'] instanceof Domain) {
            $mResult[self::GetName() . '::SignatureId'] = $aArgs['Domain']->getExtendedProp(self::GetName() . '::SignatureId');
        }
    }

    public function onMailAccountToResponseArray($aArgs, &$mResult)
    {
        if (is_array($aArgs) && isset($aArgs['Account']) && $aArgs['Account'] instanceof MailAccount) {
            $mailAccount = $aArgs['Account'];
            $user = Api::getUserById($mailAccount->IdUser);
            if ($user instanceof User) {
                if ($user->PublicId === $mailAccount->Email && $user->getExtendedProp(self::GetName() . '::UseGlobalSignature', false)) {
                    $domainId = $user->getExtendedProp('MailDomains::DomainId', false);
                    if ($domainId) {
                        $domain = Domain::find($domainId);
                        if ($domain) {
                            $signatureId = $domain->getExtendedProp(self::GetName() . '::SignatureId');
                            if (is_int($signatureId)) {
                                $signature = GlobalSignature::find($signatureId);
                                if ($signature) {

                                    $mResult['UseSignature'] = true;
                                    $mResult['AllowEditSignature'] = false;
                                    $mResult['AllowUseIdentities'] = $user->getExtendedProp(self::GetName() . '::AllowCreatingIdentities', false);
                                    $mResult['Signature'] = str_replace(
                                        ['{{Name}}', '{{Position}}', '{{Phone}}', '{{Email}}', '{{Optional}}'],
                                        [
                                            $user->getExtendedProp(self::GetName() . '::Name', ''),
                                            $user->getExtendedProp(self::GetName() . '::Position', ''),
                                            $user->getExtendedProp(self::GetName() . '::Phone', ''),
                                            $user->getExtendedProp(self::GetName() . '::Email', ''),
                                            $user->getExtendedProp(self::GetName() . '::Optional', ''),
                                        ],
                                        $signature->Signature
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
