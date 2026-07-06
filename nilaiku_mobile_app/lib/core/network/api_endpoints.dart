class ApiEndpoints {
  static const baseUrl = 'https://uas-ppb-c050424006-backend.test/api';

  static String get portalBaseUrl => baseUrl.replaceFirst('/api', '');

  // Auth endpoints
  static const login = '$baseUrl/login';
  static const register = '$baseUrl/register';
  static const logout = '$baseUrl/logout';

  // User endpoints
  static const me = '$baseUrl/me';
  static const profile = '$baseUrl/profile';
  static const profilePhoto = '$baseUrl/profile/photo';

  // Academic endpoints
  static const tahunAkademik = '$baseUrl/tahun-akademik';
  static const mataKuliah = '$baseUrl/mata-kuliah';
  static const komponenNilai = '$baseUrl/komponen-nilai';
  static const nilaiAkhir = '$baseUrl/nilai-akhir';
  static const ipsIpk = '$baseUrl/ips-ipk';

  // Rekapitulasi endpoint
  static const rekapitulasi = '$baseUrl/rekapitulasi';
  static const rekapitulasiPdfUrl = '$baseUrl/rekapitulasi/pdf-url';

  // Grading templates
  static const gradingTemplates = '$baseUrl/grading-templates';

  // Dashboard
  static const dashboardStats = '$baseUrl/dashboard-stats';
}
