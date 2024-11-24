<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Support\Facades\Mail;

class TwoFactorService
{
  protected $codeLength = 6;

  public function generateCode()
  {
    return str_pad(rand(0, 999999), $this->codeLength, '0', STR_PAD_LEFT);
  }

  public function setCode($user, $deviceId)
  {
    $code = $this->generateCode();
    $expiry = (int) env('TWO_FA_EXPIRY', 10);
    $user->update([
      'two_fa_code' => Hash::make($code),
      'two_fa_expires_at' => now()->addMinutes($expiry),
      'two_fa_device_id' => $deviceId,
    ]);

    // Отправляем код на email пользователя
    Mail::to($user->email)->send(new TwoFactorCodeMail($code));
    return $code;
  }

  public function isValid($code, $user)
  {
    return Hash::check($code, $user->two_fa_code) && $user->two_fa_expires_at->isFuture();
  }

  public function clearCode($user)
  {
    $user->update([
      'two_fa_code' => null,
      'two_fa_expires_at' => null,
      'two_fa_device_id' => null,
    ]);
  }
}
