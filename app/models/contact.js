class Contact {
  constructor(db) {
    this.db = db;
  }

  async createMessage(data) {
    const [result] = await this.db.execute(
      `INSERT INTO contact_messages
       (userId, firstName, lastName, email, subject, message, status)
       VALUES (?, ?, ?, ?, ?, ?, 'unread')`,
      [
        data.userId,
        data.firstName,
        data.lastName,
        data.email,
        data.subject,
        data.message,
      ],
    );

    return result.insertId;
  }

  async getAllMessages() {
    const [rows] = await this.db.execute(
      `SELECT
         id,
         userId,
         firstName,
         lastName,
         email,
         subject,
         message,
         status,
         created_at
       FROM contact_messages
       ORDER BY created_at DESC`,
    );

    return rows;
  }

  async getMessageById(id) {
    const [rows] = await this.db.execute(
      `SELECT
         id,
         userId,
         firstName,
         lastName,
         email,
         subject,
         message,
         status,
         created_at
       FROM contact_messages
       WHERE id = ?
       LIMIT 1`,
      [id],
    );

    return rows[0] || null;
  }

  async updateStatus(id, status) {
    const [result] = await this.db.execute(
      `UPDATE contact_messages
       SET status = ?
       WHERE id = ?`,
      [status, id],
    );

    return result.affectedRows > 0;
  }
}

module.exports = Contact;
