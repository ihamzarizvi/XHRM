/**
 * TOTP Service for generating Time-based One-Time Passwords
 * Implements HMAC-SHA1 algorithm.
 */
export class TotpService {
  static async generate(secret: string): Promise<string> {
    if (!secret) return '';

    // Clean secret
    const key = this.base32ToBuf(secret.toUpperCase().replace(/ /g, ''));
    if (!key) return 'Invalid Key';

    const epoch = Math.round(new Date().getTime() / 1000.0);
    const time = new Uint8Array(8);

    // Write time in big-endian (counter)
    const counter = Math.floor(epoch / 30);
    // JS max integer support is tricky for 64-bit writing without BigInt,
    // but for current epoch 32-bit fits in the lower bytes.
    // Simplified writing for typical timeframe:
    new DataView(time.buffer).setBigUint64(0, BigInt(counter), false); // Big-endian

    // HMAC-SHA1
    const cryptoKey = await window.crypto.subtle.importKey(
      'raw',
      key,
      {name: 'HMAC', hash: 'SHA-1'},
      false,
      ['sign'],
    );

    const signature = await window.crypto.subtle.sign('HMAC', cryptoKey, time);

    const hmac = new Uint8Array(signature);
    const offset = hmac[hmac.length - 1] & 0xf;

    const binary =
      ((hmac[offset] & 0x7f) << 24) |
      ((hmac[offset + 1] & 0xff) << 16) |
      ((hmac[offset + 2] & 0xff) << 8) |
      (hmac[offset + 3] & 0xff);

    let otp = (binary % 1000000).toString();
    while (otp.length < 6) {
      otp = '0' + otp;
    }

    return otp;
  }

  private static base32ToBuf(str: string): Uint8Array | null {
    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    let bits = 0;
    let value = 0;
    let index = 0; // write index
    const output = new Uint8Array(Math.ceil((str.length * 5) / 8));

    for (let i = 0; i < str.length; i++) {
      const char = str[i];
      const idx = alphabet.indexOf(char);
      if (idx === -1) return null;

      value = (value << 5) | idx;
      bits += 5;

      if (bits >= 8) {
        output[index++] = (value >>> (bits - 8)) & 255;
        bits -= 8;
      }
    }
    return output;
  }
}
