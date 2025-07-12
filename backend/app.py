from flask import Flask, jsonify, request
import mysql.connector
import pandas as pd
from sklearn.tree import DecisionTreeClassifier, export_graphviz
from flask_cors import CORS
import json
import graphviz
from sklearn.metrics import accuracy_score, confusion_matrix, classification_report


app = Flask(__name__)
CORS(app)

def get_data():
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="c45"
    )
    df = pd.read_sql("""
        SELECT r.id_responden, k.nama_kriteria, nk.nilai
        FROM penilaian p
        JOIN responden r ON r.id_responden = p.id_responden
        JOIN kriteria k ON p.id_kriteria = k.id_kriteria
        JOIN nilai_kriteria nk ON p.id_nilai = nk.id_nilai
    """, conn)
    conn.close()
    return df

# Konversi sklearn.tree ke format JSON D3.js
def build_tree(clf, feature_names, y_labels):
    tree = clf.tree_

    def recurse(node):
        if tree.feature[node] != -2:
            name = feature_names[tree.feature[node]]
            children = []
            if tree.children_left[node] != -1:
                children.append(recurse(tree.children_left[node]))
            if tree.children_right[node] != -1:
                children.append(recurse(tree.children_right[node]))
            return {
                "name": name,
                "children": children
            }
        else:
            values = tree.value[node][0]
            kelas = values.argmax()
            return { "name": f"Keputusan: {y_labels[kelas]}" }

    return recurse(0)

@app.route('/c45/run', methods=['GET'])
def run_c45():
    df = get_data()
    pivot = df.pivot(index='id_responden', columns='nama_kriteria', values='nilai').reset_index()

    if 'Kepuasan' not in pivot.columns:
        return jsonify({'error': 'Kolom Kepuasan tidak ditemukan'}), 400

    X = pivot.drop(columns=['id_responden', 'Kepuasan'])
    y = pivot['Kepuasan']

    # Encode kategori
    X_encoded = X.apply(lambda col: pd.factorize(col)[0])
    y_encoded, y_labels = pd.factorize(y)

    # Train decision tree
    clf = DecisionTreeClassifier(criterion='entropy', max_depth=5)
    clf.fit(X_encoded, y_encoded)

    # 1. Simpan ke JSON (untuk D3.js)
    json_tree = build_tree(clf, list(X.columns), list(y_labels))
    with open("tree_decision_c45.json", "w") as f:
        json.dump(json_tree, f, indent=2)

    # 2. Simpan ke PNG (Graphviz)
    dot_data = export_graphviz(
        clf,
        out_file=None,
        feature_names=X.columns,
        class_names=list(y_labels),
        filled=True,
        rounded=True,
        special_characters=True
    )
    graph = graphviz.Source(dot_data)
    graph.render("tree_decision_c45", format="png", cleanup=True)  # menghasilkan tree_decision_c45.png

    return jsonify({
        'message': 'Analisis berhasil diproses',
        'json_saved': 'tree_decision_c45.json',
        'image_saved': 'tree_decision_c45.png'
    })

from sklearn.metrics import accuracy_score, confusion_matrix, classification_report

@app.route('/c45/akurasi', methods=['GET'])
def cek_akurasi():
    df = get_data()
    pivot = df.pivot(index='id_responden', columns='nama_kriteria', values='nilai').reset_index()

    if 'Kepuasan' not in pivot.columns:
        return jsonify({'error': 'Kolom Kepuasan tidak ditemukan'}), 400

    X = pivot.drop(columns=['id_responden', 'Kepuasan'])
    y = pivot['Kepuasan']

    # Encode kategori
    X_encoded = X.apply(lambda col: pd.factorize(col)[0])
    y_encoded, y_labels = pd.factorize(y)

    # Train decision tree
    clf = DecisionTreeClassifier(criterion='entropy', max_depth=5)
    clf.fit(X_encoded, y_encoded)

    # Prediksi & evaluasi
    y_pred = clf.predict(X_encoded)
    akurasi = accuracy_score(y_encoded, y_pred)
    conf_matrix = confusion_matrix(y_encoded, y_pred)

    # Manual Precision, Recall, F1 (untuk kelas 0 / 'Puas')
    if conf_matrix.shape == (2, 2):  # binary classification
        TP = conf_matrix[0][0]
        FN = conf_matrix[0][1]
        FP = conf_matrix[1][0]

        precision = TP / (TP + FP) if (TP + FP) > 0 else 0
        recall = TP / (TP + FN) if (TP + FN) > 0 else 0
        f1_score = 2 * precision * recall / (precision + recall) if (precision + recall) > 0 else 0
    else:
        precision = recall = f1_score = None  # not binary

    # Optional: tetap sertakan laporan lengkap dari sklearn
    laporan = classification_report(y_encoded, y_pred, target_names=y_labels)

    return jsonify({
        'akurasi': akurasi,
        'precision': precision,
        'recall': recall,
        'f1_score': f1_score,
        'confusion_matrix': conf_matrix.tolist(),
        'classification_report': laporan,
        'kelas_positif': y_labels[0] if len(y_labels) >= 1 else None
    })

if __name__ == '__main__':
    app.run(debug=True)
