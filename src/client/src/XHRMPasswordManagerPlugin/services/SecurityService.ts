/**
 * Security Service for client-side encryption.
 * Currently a placeholder using Base64.
 * TODO: Implement AES-256-GCM using Web Crypto API.
 */
export class SecurityService {
    static encrypt(data: string, key?: string): string {
        if (!data) return '';
        // Real implementation would use the key
        return "ENC::" + btoa(data);
    }

    static decrypt(data: string, key?: string): string {
        if (!data) return '';
        if (data.startsWith("ENC::")) {
            return atob(data.substring(5));
        }
        return data;
    }
}
