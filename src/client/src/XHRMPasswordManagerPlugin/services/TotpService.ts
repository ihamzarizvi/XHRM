/* eslint-disable no-console */
import * as OTPAuth from 'otpauth';

/**
 * Service for handling Time-based One-Time Passwords (TOTP).
 * Wraps the 'otpauth' library to provide simple generation and verification methods.
 */
export class TOTPService {
  /**
   * Generates a TOTP code for the given secret at the current time.
   * @param secret The base32 encoded secret key
   * @returns The generated 6-digit code or null if secret is invalid
   */
  static generateCode(secret: string): string | null {
    if (!secret) return null;

    try {
      // Remove spaces/dashes and handle potential padding
      const cleanSecret = secret.replace(/[\s-]/g, '').toUpperCase();

      const totp = new OTPAuth.TOTP({
        issuer: 'XHRM Vault',
        label: 'User',
        algorithm: 'SHA1',
        digits: 6,
        period: 30,
        secret: cleanSecret, // otpauth handles base32 decoding
      });

      return totp.generate();
    } catch (e) {
      console.error('Failed to generate TOTP code:', e);
      return null;
    }
  }

  /**
   * Validates a TOTP code against a secret.
   * Allows for a 1-period variance (window of 1).
   */
  static validate(secret: string, token: string): boolean {
    if (!secret || !token) return false;

    try {
      const cleanSecret = secret.replace(/[\s-]/g, '').toUpperCase();
      const totp = new OTPAuth.TOTP({
        algorithm: 'SHA1',
        digits: 6,
        period: 30,
        secret: cleanSecret,
      });

      const delta = totp.validate({token, window: 1});
      return delta !== null;
    } catch (e) {
      console.error('Failed to validate TOTP code:', e);
      return false;
    }
  }

  /**
   * Returns the remaining seconds in the current 30-second epoch.
   * Useful for UI countdown timers.
   */
  static getRemainingSeconds(): number {
    const epoch = Math.floor(Date.now() / 1000);
    return 30 - (epoch % 30);
  }

  /**
   * Generates a random Base32 secret key for new TOTP setups.
   * (Simple implementation for client-side generation)
   */
  static generateSecret(length = 20): string {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    let secret = '';
    const randomValues = new Uint8Array(length);
    window.crypto.getRandomValues(randomValues);

    for (let i = 0; i < length; i++) {
      secret += chars[randomValues[i] % chars.length];
    }
    return secret;
  }
}
