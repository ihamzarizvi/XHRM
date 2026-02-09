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
  private static readonly SALT_LENGTH = 16;
  private static readonly IV_LENGTH = 12; // Standard for GCM
  private static readonly ITERATIONS = 100000;

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
  static async unlockVault(password: string, saltHex: string): Promise<void> {
    const salt = this.hexToBytes(saltHex);
    this.masterKey = await this.deriveKey(password, salt);
  }

  static isUnlocked(): boolean {
    return this.masterKey !== null;
  }

  static lockVault(): void {
    this.masterKey = null;
  }

  /**
   * Encrypts data using AES-256-GCM.
   * Returns format: `IV::CIPHERTEXT` (both base64 encoded)
   */
  static async encrypt(data: string): Promise<string> {
    if (!this.masterKey) throw new Error('Vault is locked');
    if (!data) return '';

    const iv = window.crypto.getRandomValues(new Uint8Array(this.IV_LENGTH));
    const enc = new TextEncoder();

    const encryptedContent = await window.crypto.subtle.encrypt(
      {
        name: 'AES-GCM',
        iv: iv as any,
      },
      this.masterKey,
      enc.encode(data) as any,
    );

    const ivBase64 = this.arrayBufferToBase64(iv);
    const contentBase64 = this.arrayBufferToBase64(encryptedContent);

    return `${ivBase64}::${contentBase64}`;
  }

  /**
   * Decrypts data.
   * Expects format: `IV::CIPHERTEXT`
   */
  static async decrypt(encryptedData: string): Promise<string> {
    if (!this.masterKey) throw new Error('Vault is locked');
    if (!encryptedData || !encryptedData.includes('::')) return '';

    try {
      const [ivBase64, contentBase64] = encryptedData.split('::');
      const iv = this.base64ToArrayBuffer(ivBase64);
      const content = this.base64ToArrayBuffer(contentBase64);

      const decryptedContent = await window.crypto.subtle.decrypt(
        {
          name: 'AES-GCM',
          iv: iv as any,
        },
        this.masterKey,
        content as any,
      );

      const dec = new TextDecoder();
      return dec.decode(decryptedContent);
    } catch (e) {
      console.error('Decryption failed', e);
      return '[Encrypted Data]';
    }
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

  private static hexToBytes(hex: string): Uint8Array {
    const bytes = new Uint8Array(hex.length / 2);
    for (let i = 0; i < hex.length; i += 2) {
      bytes[i / 2] = parseInt(hex.substring(i, i + 2), 16);
    }
    return bytes;
  }
}
