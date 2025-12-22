import sqlite3
import datetime
from flask import Flask, render_template, request, jsonify, abort

app = Flask(__name__)
DB_NAME = 'baby_names.db'

def get_db_connection():
    conn = sqlite3.connect(DB_NAME)
    conn.row_factory = sqlite3.Row
    return conn

def init_db():
    conn = get_db_connection()
    conn.execute('''
        CREATE TABLE IF NOT EXISTS names (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            definition TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ''')
    conn.commit()
    conn.close()

# Initialize DB on startup
with app.app_context():
    init_db()

@app.context_processor
def inject_now():
    return {'now': datetime.datetime.now()}

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/name/<int:name_id>')
def name_detail(name_id):
    conn = get_db_connection()
    name = conn.execute('SELECT * FROM names WHERE id = ?', (name_id,)).fetchone()
    conn.close()
    if name is None:
        abort(404)
    return render_template('name_detail.html', name=name)

@app.route('/admin')
def admin():
    conn = get_db_connection()
    pending_names = conn.execute('SELECT * FROM names WHERE status = "pending" ORDER BY created_at DESC').fetchall()
    all_names = conn.execute('SELECT * FROM names ORDER BY created_at DESC').fetchall()
    conn.close()
    return render_template('admin.html', pending_names=pending_names, all_names=all_names)

# API
@app.route('/api/names', methods=['GET', 'POST'])
def api_names():
    conn = get_db_connection()

    if request.method == 'GET':
        action = request.args.get('action', 'list')

        if action == 'search':
            query = request.args.get('q', '')
            names = conn.execute('SELECT * FROM names WHERE status = "approved" AND (name LIKE ? OR definition LIKE ?)',
                                 ('%' + query + '%', '%' + query + '%')).fetchall()
            conn.close()
            return jsonify([dict(ix) for ix in names])

        elif action == 'suggestions':
            # Random 5 approved names
            names = conn.execute('SELECT * FROM names WHERE status = "approved" ORDER BY RANDOM() LIMIT 5').fetchall()
            conn.close()
            return jsonify([dict(ix) for ix in names])

        else: # list
            names = conn.execute('SELECT * FROM names WHERE status = "approved" ORDER BY created_at DESC').fetchall()
            conn.close()
            return jsonify([dict(ix) for ix in names])

    elif request.method == 'POST':
        data = request.json
        if not data or 'name' not in data or 'definition' not in data:
            conn.close()
            return jsonify({'success': False, 'error': 'Invalid input'}), 400

        conn.execute('INSERT INTO names (name, definition, status) VALUES (?, ?, ?)',
                     (data['name'], data['definition'], 'pending')) # Default to pending
        conn.commit()
        conn.close()
        return jsonify({'success': True})

@app.route('/api/names/<int:name_id>/approve', methods=['POST'])
def approve_name(name_id):
    conn = get_db_connection()
    conn.execute('UPDATE names SET status = "approved" WHERE id = ?', (name_id,))
    conn.commit()
    conn.close()
    return jsonify({'success': True})

@app.route('/api/names/<int:name_id>/reject', methods=['POST'])
def reject_name(name_id):
    conn = get_db_connection()
    conn.execute('UPDATE names SET status = "rejected" WHERE id = ?', (name_id,))
    conn.commit()
    conn.close()
    return jsonify({'success': True})

if __name__ == '__main__':
    app.run(debug=True, port=5000)
