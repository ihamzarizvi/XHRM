/* eslint-disable no-console, @typescript-eslint/no-explicit-any */
/**
 * Security Service for client-side encryption using Web Crypto API.
 * Algorithms:
 * - Encryption: AES-256-GCM
 * - Key Derivation: PBKDF2 (SHA-256, 100,000 iterations)
 * - Randomness: window.crypto.getRandomValues
 */
export class SecurityService {
  private static masterKey: CryptoKey | null = null;
  private static privateKey: CryptoKey | null = null;
  private static readonly SALT_LENGTH = 16;
  private static readonly IV_LENGTH = 12; // Standard for GCM
  private static readonly ITERATIONS = 100000;
  private static masterKeyVersion = 0; // increments each time masterKey is set

  /**
   * App-level secret combined with the server salt for key derivation.
   * This is NOT a user password — it's a fixed constant that makes
   * the derived key unique to this application.
   */
  private static readonly APP_SECRET = 'XHRM_VAULT_APP_SECRET_V1';

  /**
   * Derives an AES-GCM key from a user's master password.
   * This key should be kept in memory and never stored.
   * For the MVP, we derive it fresh or store it in a secure runtime variable.
   */
  static async deriveKey(
    password: string,
    salt: Uint8Array,
  ): Promise<CryptoKey> {
    const enc = new TextEncoder();
    const keyMaterial = await window.crypto.subtle.importKey(
      'raw',
      enc.encode(password),
      'PBKDF2',
      false,
      ['deriveKey'],
    );

    return window.crypto.subtle.deriveKey(
      {
        name: 'PBKDF2',
        salt: salt as any,
        iterations: this.ITERATIONS,
        hash: 'SHA-256',
      },
      keyMaterial,
      {name: 'AES-GCM', length: 256},
      false, // non-extractable
      ['encrypt', 'decrypt'],
    );
  }

  /**
   * Initialize the service with a master password.
   * In a real app, the salt should be user-specific and constant (stored in DB).
   * For this MVP, we'll use a deterministic salt based on the username or a fixed app salt if necessary,
   * BUT ideally, we generate a random salt per item or use a master salt.
   *
   * @param password - The user's master password
   * @param saltHex - The user's unique salt (hex string)
   */
  /**
   * Auto-unlock: derives the master key from a server-provided hex salt.
   * No user password needed — the salt is unique per user and stored on the server.
   * Key = PBKDF2(APP_SECRET, serverSalt, 100000 iterations, SHA-256)
   */
  static async autoUnlock(saltHex: string): Promise<void> {
    const salt = this.hexToBytes(saltHex);
    this.masterKey = await this.deriveKey(this.APP_SECRET, salt);
    this.masterKeyVersion++;
    console.log(
      '[VAULT DEBUG] masterKey set, version:',
      this.masterKeyVersion,
      'salt prefix:',
      saltHex.substring(0, 16),
    );
  }

  /**
   * Legacy: unlock with user-provided password (kept for compatibility).
   */
  static async unlockVault(password: string, saltHex?: string): Promise<void> {
    const enc = new TextEncoder();
    const salt = saltHex
      ? this.hexToBytes(saltHex)
      : enc.encode('XHRM_VAULT_SALT_V1');
    this.masterKey = await this.deriveKey(password, salt);
  }

  static isVaultUnlocked(): boolean {
    return this.masterKey !== null;
  }

  static lockVault(): void {
    this.masterKey = null;
    this.privateKey = null;
  }

  static setPrivateKey(key: CryptoKey): void {
    this.privateKey = key;
  }

  static getPrivateKey(): CryptoKey | null {
    return this.privateKey;
  }

  /**
   * Encrypts data using AES-256-GCM.
   * Returns format: `IV::CIPHERTEXT` (both base64 encoded)
   */
  static async encrypt(data: string, key?: CryptoKey): Promise<string> {
    const useKey = key || this.masterKey;
    if (!useKey) throw new Error('Vault is locked and no key provided');
    if (!data) return '';

    const usingMaster = !key;
    if (usingMaster) {
      console.log(
        '[VAULT DEBUG] encrypt() using masterKey v' + this.masterKeyVersion,
      );
    }

    const iv = window.crypto.getRandomValues(new Uint8Array(this.IV_LENGTH));
    const enc = new TextEncoder();

    const encryptedContent = await window.crypto.subtle.encrypt(
      {
        name: 'AES-GCM',
        iv: iv as any,
      },
      useKey,
      enc.encode(data) as any,
    );

    const ivBase64 = this.arrayBufferToBase64(iv.buffer);
    const contentBase64 = this.arrayBufferToBase64(encryptedContent);

    return `${ivBase64}::${contentBase64}`;
  }

  /**
   * Decrypts data.
   * Expects format: `IV::CIPHERTEXT`
   */
  static async decrypt(
    encryptedData: string,
    key?: CryptoKey,
  ): Promise<string> {
    const useKey = key || this.masterKey;
    if (!useKey) throw new Error('Vault is locked and no key provided');
    if (!encryptedData || !encryptedData.includes('::')) return '';

    const usingMaster = !key;
    if (usingMaster) {
      console.log(
        '[VAULT DEBUG] decrypt() using masterKey v' +
          this.masterKeyVersion +
          ' data prefix:',
        encryptedData.substring(0, 20),
      );
    }

    try {
      const parts = encryptedData.split('::');
      const ivBase64 = parts[0];
      const contentBase64 = parts.slice(1).join('::'); // rejoin in case base64 somehow had ::
      // Fix potential space-to-plus corruption from server/transport
      const safeIv = ivBase64.replace(/ /g, '+');
      const safeContent = contentBase64.replace(/ /g, '+');

      const iv = this.base64ToArrayBuffer(safeIv);
      const content = this.base64ToArrayBuffer(safeContent);

      const decryptedContent = await window.crypto.subtle.decrypt(
        {
          name: 'AES-GCM',
          iv: iv as any,
        },
        useKey,
        content as any,
      );

      const dec = new TextDecoder();
      return dec.decode(decryptedContent);
    } catch (e) {
      console.error('Decryption failed', e);
      return '[Encrypted Data]';
    }
  }

  // --- AES Key Management (Item Keys) ---

  static async generateAESKey(): Promise<CryptoKey> {
    return window.crypto.subtle.generateKey(
      {
        name: 'AES-GCM',
        length: 256,
      },
      true,
      ['encrypt', 'decrypt'],
    );
  }

  static async exportAESKey(key: CryptoKey): Promise<string> {
    const exported = await window.crypto.subtle.exportKey('raw', key);
    return this.arrayBufferToBase64(exported);
  }

  static async importAESKey(keyData: string): Promise<CryptoKey> {
    const raw = this.base64ToArrayBuffer(keyData);
    return window.crypto.subtle.importKey('raw', raw as any, 'AES-GCM', true, [
      'encrypt',
      'decrypt',
    ]);
  }

  // --- RSA-OAEP (Public Key Infrastructure) ---

  static async generateKeyPair(): Promise<CryptoKeyPair> {
    return window.crypto.subtle.generateKey(
      {
        name: 'RSA-OAEP',
        modulusLength: 2048,
        publicExponent: new Uint8Array([1, 0, 1]),
        hash: 'SHA-256',
      },
      true,
      ['encrypt', 'decrypt'],
    );
  }

  static async exportKey(key: CryptoKey): Promise<string> {
    const format = key.type === 'public' ? 'spki' : 'pkcs8';
    const exported = await window.crypto.subtle.exportKey(format, key);
    return this.arrayBufferToBase64(exported);
  }

  static async importKey(
    keyData: string,
    type: 'public' | 'private',
  ): Promise<CryptoKey> {
    const format = type === 'public' ? 'spki' : 'pkcs8';
    const binary = this.base64ToArrayBuffer(keyData);
    return window.crypto.subtle.importKey(
      format,
      binary as any,
      {
        name: 'RSA-OAEP',
        hash: 'SHA-256',
      },
      true,
      [type === 'public' ? 'encrypt' : 'decrypt'],
    );
  }

  static async encryptRSA(data: string, publicKey: CryptoKey): Promise<string> {
    const enc = new TextEncoder();
    const encrypted = await window.crypto.subtle.encrypt(
      {name: 'RSA-OAEP'},
      publicKey,
      enc.encode(data) as any,
    );
    return this.arrayBufferToBase64(encrypted);
  }

  static async decryptRSA(
    encryptedData: string,
    privateKey: CryptoKey,
  ): Promise<string> {
    const data = this.base64ToArrayBuffer(encryptedData);
    const decrypted = await window.crypto.subtle.decrypt(
      {name: 'RSA-OAEP'},
      privateKey,
      data as any,
    );
    const dec = new TextDecoder();
    return dec.decode(decrypted);
  }

  // --- Utilities ---

  private static arrayBufferToBase64(buffer: ArrayBuffer): string {
    let binary = '';
    const bytes = new Uint8Array(buffer);
    for (let i = 0; i < bytes.byteLength; i++) {
      binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
  }

  private static base64ToArrayBuffer(base64: string): Uint8Array {
    const binary_string = window.atob(base64);
    const len = binary_string.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
      bytes[i] = binary_string.charCodeAt(i);
    }
    return bytes;
  }

  static hexToBytes(hex: string): Uint8Array {
    const bytes = new Uint8Array(hex.length / 2);
    for (let i = 0; i < hex.length; i += 2) {
      bytes[i / 2] = parseInt(hex.substring(i, i + 2), 16);
    }
    return bytes;
  }

  // --- Password Analysis & Generation ---

  static generatePassword(
    length = 16,
    useUpper = true,
    useLower = true,
    useNumbers = true,
    useSymbols = true,
  ): string {
    let charset = '';
    if (useLower) charset += 'abcdefghijklmnopqrstuvwxyz';
    if (useUpper) charset += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if (useNumbers) charset += '0123456789';
    if (useSymbols) charset += '!@#$%^&*()_+~`|}{[]:;?><,./-=';

    if (!charset) return '';

    const result = new Uint8Array(length);
    window.crypto.getRandomValues(result);

    return Array.from(result)
      .map((x) => charset[x % charset.length])
      .join('');
  }

  /**
   * Returns a score from 0 to 100 based on password strength.
   * Simple heuristic: length + variety + patterns
   */
  static assessPasswordStrength(password: string): number {
    if (!password) return 0;
    let score = 0;

    // Length contribution (up to 40 points)
    score += Math.min(40, password.length * 4);

    // Variety contribution (up to 40 points)
    let varietyCount = 0;
    if (/[a-z]/.test(password)) varietyCount++;
    if (/[A-Z]/.test(password)) varietyCount++;
    if (/[0-9]/.test(password)) varietyCount++;
    if (/[^a-zA-Z0-9]/.test(password)) varietyCount++;
    score += varietyCount * 10;

    // Penalty for repeated characters (e.g. 'aaaa')
    if (/(.)\1{2,}/.test(password)) score -= 10;

    // Penalty for common patterns (very basic check)
    if (
      ['123456', 'password', 'qwerty'].some((p) =>
        password.toLowerCase().includes(p),
      )
    ) {
      score -= 30;
    }

    return Math.max(0, Math.min(100, score));
  }
}
