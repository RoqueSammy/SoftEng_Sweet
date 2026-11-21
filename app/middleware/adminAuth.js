function requireAdmin(req, res, next) {
  if (!req.session || !req.session.authUser) {
    return res.status(401).json({
      success: false,
      message: "Please log in first.",
    });
  }

  const role = req.session.userRole || req.session.authUser.role;

  if (role !== "admin") {
    return res.status(403).json({
      success: false,
      message: "Access denied. Admin privileges required.",
    });
  }

  next();
}

module.exports = requireAdmin;
