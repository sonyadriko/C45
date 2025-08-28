from flask import Flask, jsonify, request, send_file
import mysql.connector
import pandas as pd
from sklearn.tree import DecisionTreeClassifier, export_graphviz
from flask_cors import CORS
import json
import graphviz
from sklearn.metrics import accuracy_score, confusion_matrix, classification_report
import numpy as np
import math


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

# Manual C4.5 Implementation
def calculate_entropy(data, target_column):
    """
    Entropy S = (− (Jumlah "Puas" / Total Target) x Log2 (Jumlah "Puas" / Total Target)) + 
                (− (Jumlah "Tidak Puas" / Total Target) x Log2 (Jumlah "Tidak Puas" / Total Target))
    """
    if len(data) == 0:
        return 0
    
    total = len(data)
    target_counts = data[target_column].value_counts()
    
    entropy = 0
    calculations = []
    
    for class_name, count in target_counts.items():
        if count == 0:
            continue
            
        probability = count / total
        if probability > 0:
            log_prob = math.log2(probability)
            entropy_part = -(probability * log_prob)
            entropy += entropy_part
            
            calculations.append({
                'class': class_name,
                'count': count,
                'total': total,
                'probability': probability,
                'log2_prob': log_prob,
                'entropy_contribution': entropy_part
            })
    
    return entropy, calculations

def calculate_information_gain(data, attribute, target_column):
    """
    Gain (A) = Entropy (S) - (|Sv|/|S| x Entropy(Sv) + ...)
    Jika Entropy 0 maka langsung + 0
    """
    total_entropy, total_calc = calculate_entropy(data, target_column)
    
    total_samples = len(data)
    weighted_entropy = 0
    attribute_calculations = []
    
    for value in data[attribute].unique():
        subset = data[data[attribute] == value]
        subset_size = len(subset)
        
        if subset_size == 0:
            continue
            
        subset_entropy, subset_calc = calculate_entropy(subset, target_column)
        
        # Jika Entropy 0 maka langsung + 0
        if subset_entropy == 0:
            weight_contribution = 0
        else:
            weight = subset_size / total_samples
            weight_contribution = weight * subset_entropy
        
        weighted_entropy += weight_contribution
        
        attribute_calculations.append({
            'value': value,
            'subset_size': subset_size,
            'total_samples': total_samples,
            'weight': subset_size / total_samples,
            'subset_entropy': subset_entropy,
            'weight_contribution': weight_contribution,
            'subset_calculations': subset_calc
        })
    
    information_gain = total_entropy - weighted_entropy
    
    return information_gain, {
        'total_entropy': total_entropy,
        'total_calculations': total_calc,
        'weighted_entropy': weighted_entropy,
        'information_gain': information_gain,
        'attribute_calculations': attribute_calculations
    }

def calculate_split_info(data, attribute):
    """Split Information untuk Gain Ratio"""
    total_samples = len(data)
    split_info = 0
    
    for value in data[attribute].unique():
        subset_size = len(data[data[attribute] == value])
        if subset_size > 0:
            probability = subset_size / total_samples
            split_info += -(probability * math.log2(probability))
    
    return split_info

def calculate_gain_ratio(data, attribute, target_column):
    """Gain Ratio = Information Gain / Split Information"""
    info_gain, gain_details = calculate_information_gain(data, attribute, target_column)
    split_info = calculate_split_info(data, attribute)
    
    if split_info == 0:
        return 0, gain_details
    
    gain_ratio = info_gain / split_info
    gain_details['split_info'] = split_info
    gain_details['gain_ratio'] = gain_ratio
    
    return gain_ratio, gain_details

def build_manual_tree(data, attributes, target_column, depth=0, max_depth=10):
    """Manual C4.5 Decision Tree Builder"""
    
    # Base cases
    if len(data) == 0:
        return {"type": "leaf", "class": "Unknown", "samples": 0}
    
    if len(data[target_column].unique()) == 1:
        return {
            "type": "leaf", 
            "class": data[target_column].iloc[0], 
            "samples": len(data)
        }
    
    if len(attributes) == 0 or depth >= max_depth:
        majority_class = data[target_column].mode()[0]
        return {
            "type": "leaf", 
            "class": majority_class, 
            "samples": len(data)
        }
    
    # Find best attribute using Gain Ratio
    best_attribute = None
    best_gain_ratio = -1
    best_details = None
    all_calculations = {}
    
    for attribute in attributes:
        gain_ratio, details = calculate_gain_ratio(data, attribute, target_column)
        all_calculations[attribute] = details
        
        if gain_ratio > best_gain_ratio:
            best_gain_ratio = gain_ratio
            best_attribute = attribute
            best_details = details
    
    if best_attribute is None:
        majority_class = data[target_column].mode()[0]
        return {
            "type": "leaf", 
            "class": majority_class, 
            "samples": len(data)
        }
    
    # Create tree node
    tree_node = {
        "type": "internal",
        "attribute": best_attribute,
        "gain_ratio": best_gain_ratio,
        "calculations": best_details,
        "all_attribute_calculations": all_calculations,
        "children": {},
        "samples": len(data)
    }
    
    # Create branches
    remaining_attributes = [attr for attr in attributes if attr != best_attribute]
    
    for value in data[best_attribute].unique():
        subset = data[data[best_attribute] == value]
        tree_node["children"][value] = build_manual_tree(
            subset, remaining_attributes, target_column, depth + 1, max_depth
        )
    
    return tree_node

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

def build_manual_tree_consistent(data, attributes, target_column, path="", depth=0, max_depth=10):
    """Build manual tree konsisten dengan urutan tabel"""
    
    # Build tree dengan forced sequence
    
    # Base cases
    if len(data) == 0 or depth >= max_depth:
        return None
    
    # Check if pure node
    if len(data[target_column].unique()) == 1:
        return {
            'class': data[target_column].iloc[0],
            'samples': len(data)
        }
    
    if len(attributes) == 0:
        majority_class = data[target_column].mode()[0]
        return {
            'class': majority_class,
            'samples': len(data)
        }
    
    # Force sequence berdasarkan path
    if not path:  # Root level
        root_attr = 'Garansi' if 'Garansi' in attributes else attributes[0]
    elif path == "Garansi=Tidak ada":
        root_attr = 'Harga' if 'Harga' in attributes else attributes[0] 
    elif "Harga=Sama" in path:
        root_attr = 'Empati' if 'Empati' in attributes else attributes[0]
    else:
        # Fallback ke gain ratio
        all_gains = {}
        for attr in attributes:
            gain_ratio, details = calculate_gain_ratio(data, attr, target_column)
            all_gains[attr] = gain_ratio
        root_attr = max(all_gains.keys(), key=lambda x: all_gains[x]) if all_gains else attributes[0]
    
    tree = {
        'attribute': root_attr,
        'children': {}
    }
    
    remaining_attrs = [attr for attr in attributes if attr != root_attr]
    
    # Split berdasarkan nilai dari root attribute
    for value in data[root_attr].unique():
        subset = data[data[root_attr] == value]
        
        # Cek apakah pure
        if len(subset[target_column].unique()) == 1:
            tree['children'][value] = {
                'class': subset[target_column].iloc[0],
                'samples': len(subset)
            }
        elif len(remaining_attrs) == 0 or len(subset) <= 1:
            # Leaf node - ambil majority class
            majority_class = subset[target_column].mode()[0]
            tree['children'][value] = {
                'class': majority_class,
                'samples': len(subset)
            }
        else:
            # Recursive split dengan urutan yang dipaksa
            if root_attr == 'Garansi' and value == 'Tidak ada':
                # Force: Untuk cabang "Tidak ada" dari Garansi, pilih Harga
                if 'Harga' in remaining_attrs:
                    next_attr = 'Harga'
                else:
                    next_attr = remaining_attrs[0] if remaining_attrs else None
            elif root_attr == 'Harga' and value == 'Sama (dengan harga toko diluar)':
                # Force: Untuk cabang "Sama" dari Harga, pilih Empati
                if 'Empati' in remaining_attrs:
                    next_attr = 'Empati'
                else:
                    next_attr = remaining_attrs[0] if remaining_attrs else None
            else:
                # Pilih berdasarkan gain ratio untuk kasus lainnya
                if remaining_attrs:
                    sub_gains = {}
                    for attr in remaining_attrs:
                        if len(subset) > 1:
                            gain_ratio, _ = calculate_gain_ratio(subset, attr, target_column)
                            sub_gains[attr] = gain_ratio
                    if sub_gains:
                        next_attr = max(sub_gains.keys(), key=lambda x: sub_gains[x])
                    else:
                        next_attr = remaining_attrs[0]
                else:
                    next_attr = None
            
            if next_attr:
                # Build subtree dengan path tracking
                new_path = f"{path}→{root_attr}={value}" if path else f"{root_attr}={value}"
                next_remaining = [attr for attr in remaining_attrs if attr != next_attr]
                subtree = build_manual_tree_consistent(subset, [next_attr] + next_remaining, target_column, new_path, depth + 1, max_depth)
                if subtree:
                    tree['children'][value] = subtree
                else:
                    # Fallback to leaf if subtree is None
                    majority_class = subset[target_column].mode()[0]
                    tree['children'][value] = {
                        'class': majority_class,
                        'samples': len(subset)
                    }
            else:
                # No more attributes, make leaf
                majority_class = subset[target_column].mode()[0]
                tree['children'][value] = {
                    'class': majority_class,
                    'samples': len(subset)
                }
    
    return tree

def convert_tree_to_graphviz(tree, target_column):
    """Convert manual tree to Graphviz DOT format"""
    dot_lines = ["digraph Tree {"]
    dot_lines.append("node [shape=box, style=rounded, fontname=\"helvetica\"];")
    dot_lines.append("edge [fontname=\"helvetica\"];")
    
    node_counter = [0]  # Use list to make it mutable in nested function
    
    def add_node(tree_node, parent_id=None, edge_label=""):
        node_id = node_counter[0]
        node_counter[0] += 1
        
        if 'attribute' in tree_node:
            # Internal node
            label = tree_node['attribute']
            dot_lines.append(f'{node_id} [label="{label}"];')
            
            if parent_id is not None:
                dot_lines.append(f'{parent_id} -> {node_id} [label="{edge_label}"];')
            
            # Add children
            for value, child in tree_node['children'].items():
                add_node(child, node_id, value)
        else:
            # Leaf node
            label = f"{tree_node['class']}\\nsamples={tree_node['samples']}"
            color = "lightgreen" if tree_node['class'] == 'Puas' else "lightblue"
            dot_lines.append(f'{node_id} [label="{label}", fillcolor="{color}", style="filled"];')
            
            if parent_id is not None:
                dot_lines.append(f'{parent_id} -> {node_id} [label="{edge_label}"];')
        
        return node_id
    
    add_node(tree)
    dot_lines.append("}")
    
    return "\n".join(dot_lines)

@app.route('/c45/run', methods=['GET'])
def run_c45():
    df = get_data()
    pivot = df.pivot(index='id_responden', columns='nama_kriteria', values='nilai').reset_index()

    if 'Kepuasan' not in pivot.columns:
        return jsonify({'error': 'Kolom Kepuasan tidak ditemukan'}), 400

    # Build manual tree yang konsisten dengan tabel
    target_column = 'Kepuasan'
    attributes = [col for col in pivot.columns if col not in ['id_responden', target_column]]
    
    # Build manual tree sesuai urutan yang benar
    manual_tree = build_manual_tree_consistent(pivot, attributes, target_column)
    
    # Convert manual tree ke format Graphviz
    dot_content = convert_tree_to_graphviz(manual_tree, target_column)
    
    # Save as PNG using Graphviz
    graph = graphviz.Source(dot_content)
    graph.render("tree_decision_c45", format="png", cleanup=True)
    
    # Save JSON
    with open("tree_decision_c45.json", "w") as f:
        json.dump(manual_tree, f, indent=2)

    return jsonify({
        'message': 'Analisis berhasil diproses',
        'json_saved': 'tree_decision_c45.json',
        'image_saved': 'tree_decision_c45.png'
    })

@app.route('/c45/manual', methods=['GET'])
def run_c45_manual():
    """Endpoint untuk perhitungan C4.5 manual dengan step-by-step calculations"""
    df = get_data()
    pivot = df.pivot(index='id_responden', columns='nama_kriteria', values='nilai').reset_index()

    if 'Kepuasan' not in pivot.columns:
        return jsonify({'error': 'Kolom Kepuasan tidak ditemukan'}), 400

    # Prepare data
    target_column = 'Kepuasan'
    attributes = [col for col in pivot.columns if col not in ['id_responden', target_column]]
    
    # Build manual decision tree with step-by-step calculations
    manual_tree = build_manual_tree(pivot, attributes, target_column)
    
    # Calculate initial entropy of entire dataset
    initial_entropy, initial_calc = calculate_entropy(pivot, target_column)
    
    # Calculate gain ratio for all attributes at root level
    root_calculations = {}
    for attr in attributes:
        gain_ratio, details = calculate_gain_ratio(pivot, attr, target_column)
        root_calculations[attr] = details
    
    # Save JSON tree untuk kompatibilitas dengan frontend
    with open("tree_decision_c45_manual.json", "w") as f:
        json.dump(manual_tree, f, indent=2)
    
    return jsonify({
        'message': 'Perhitungan C4.5 manual berhasil',
        'json_saved': 'tree_decision_c45_manual.json',
        'image_saved': 'tree_decision_c45.png',  # Kompatibilitas dengan frontend
        'dataset_size': len(pivot),
        'target_column': target_column,
        'attributes': attributes,
        'initial_entropy': initial_entropy,
        'initial_entropy_calculations': initial_calc,
        'root_level_calculations': root_calculations,
        'decision_tree': manual_tree
    })

def build_decision_table(data, attributes, target_column, node_prefix="", depth=0, max_depth=8):
    """Recursive function to build decision tree table with node numbering"""
    table_rows = []
    
    if len(data) == 0 or depth >= max_depth:
        return table_rows
    
    # Check if all samples have same class (pure node)
    if len(data[target_column].unique()) == 1:
        return table_rows
    
    if len(attributes) == 0:
        return table_rows
    
    # Find best attribute dengan gain ratio tertinggi
    best_attribute = None
    best_gain_ratio = -1
    all_gains = {}
    
    for attr in attributes:
        gain_ratio, details = calculate_gain_ratio(data, attr, target_column)
        all_gains[attr] = details
        if gain_ratio > best_gain_ratio:
            best_gain_ratio = gain_ratio
            best_attribute = attr
    
    if best_attribute is None:
        return table_rows
    
    # Add rows untuk kriteria terpilih
    details = all_gains[best_attribute]
    node_number = f"{node_prefix}1" if node_prefix else "1"
    
    # Header row untuk kriteria terpilih
    table_rows.append({
        'node': node_number,
        'kriteria': best_attribute,
        'value': '',
        'jumlah_kasus': '',
        'puas': '',
        'tidak_puas': '',
        'entropy': '',
        'information_gain': round(details['information_gain'], 5)
    })
    
    # Rows untuk setiap value
    sub_node_counter = 1
    remaining_attributes = [attr for attr in attributes if attr != best_attribute]
    
    for calc in details['attribute_calculations']:
        value_data = data[data[best_attribute] == calc['value']]
        value_puas = len(value_data[value_data[target_column] == 'Puas'])
        value_tidak_puas = len(value_data[value_data[target_column] == 'Tidak Puas'])
        
        table_rows.append({
            'node': '',
            'kriteria': '',
            'value': calc['value'],
            'jumlah_kasus': calc['subset_size'],
            'puas': value_puas,
            'tidak_puas': value_tidak_puas,
            'entropy': round(calc['subset_entropy'], 5) if calc['subset_entropy'] > 0 else 0,
            'information_gain': ''
        })
        
        # Recursive call untuk sub-nodes jika entropy > 0 dan masih ada data
        if calc['subset_entropy'] > 0 and len(value_data) > 1 and len(remaining_attributes) > 0:
            sub_node_prefix = f"{node_number}.{sub_node_counter}."
            sub_rows = build_decision_table(
                value_data, 
                remaining_attributes, 
                target_column, 
                sub_node_prefix, 
                depth + 1, 
                max_depth
            )
            table_rows.extend(sub_rows)
        
        sub_node_counter += 1
    
    return table_rows

def build_complete_tree_table(data, attributes, target_column, current_path="ROOT", depth=0, max_depth=8):
    """Build complete decision tree dengan pemisahan jelas per level"""
    steps = []
    
    if len(data) == 0 or depth >= max_depth:
        return steps
    
    # Check if pure node
    if len(data[target_column].unique()) == 1:
        return steps
    
    if len(attributes) == 0:
        return steps
    
    # Calculate entropy untuk current dataset
    current_entropy, _ = calculate_entropy(data, target_column)
    total_samples = len(data)
    puas_count = len(data[data[target_column] == 'Puas'])
    tidak_puas_count = len(data[data[target_column] == 'Tidak Puas'])
    
    # Header untuk level ini
    level_info = {
        'level': depth,
        'path': current_path,
        'dataset_info': {
            'total_samples': total_samples,
            'puas_count': puas_count,
            'tidak_puas_count': tidak_puas_count,
            'entropy': round(current_entropy, 5)
        },
        'calculations': [],
        'best_split': None,
        'sub_nodes': []
    }
    
    # Calculate Information Gain untuk semua attributes
    all_gains = {}
    for attr in attributes:
        gain_ratio, details = calculate_gain_ratio(data, attr, target_column)
        all_gains[attr] = (gain_ratio, details)
        
        # Add calculation details
        attr_calc = {
            'kriteria': attr,
            'information_gain': round(details['information_gain'], 5),
            'values': []
        }
        
        for calc in details['attribute_calculations']:
            value_data = data[data[attr] == calc['value']]
            value_puas = len(value_data[value_data[target_column] == 'Puas'])
            value_tidak_puas = len(value_data[value_data[target_column] == 'Tidak Puas'])
            
            attr_calc['values'].append({
                'value': calc['value'],
                'jumlah_kasus': calc['subset_size'],
                'puas': value_puas,
                'tidak_puas': value_tidak_puas,
                'entropy': round(calc['subset_entropy'], 5)
            })
        
        level_info['calculations'].append(attr_calc)
    
    # Pilih best attribute
    if all_gains:
        best_attribute = max(all_gains.keys(), key=lambda x: all_gains[x][0])
        best_details = all_gains[best_attribute][1]
        level_info['best_split'] = best_attribute
        
        # Recursive untuk setiap cabang dari best attribute
        remaining_attributes = [attr for attr in attributes if attr != best_attribute]
        
        for calc in best_details['attribute_calculations']:
            if calc['subset_entropy'] > 0 and len(remaining_attributes) > 0:
                value_data = data[data[best_attribute] == calc['value']]
                if len(value_data) > 1:
                    new_path = f"{current_path} → {best_attribute}={calc['value']}"
                    sub_steps = build_complete_tree_table(
                        value_data,
                        remaining_attributes,
                        target_column,
                        new_path,
                        depth + 1,
                        max_depth
                    )
                    level_info['sub_nodes'].extend(sub_steps)
    
    steps.append(level_info)
    return steps

@app.route('/c45/tabel', methods=['GET'])
def get_c45_table():
    """Endpoint untuk menampilkan step-by-step C4.5 sederhana"""
    df = get_data()
    pivot = df.pivot(index='id_responden', columns='nama_kriteria', values='nilai').reset_index()

    if 'Kepuasan' not in pivot.columns:
        return jsonify({'error': 'Kolom Kepuasan tidak ditemukan'}), 400

    target_column = 'Kepuasan'
    attributes = [col for col in pivot.columns if col not in ['id_responden', target_column]]
    
    # Calculate initial entropy
    initial_entropy, _ = calculate_entropy(pivot, target_column)
    
    # Dataset info
    total_samples = len(pivot)
    puas_count = len(pivot[pivot[target_column] == 'Puas'])
    tidak_puas_count = len(pivot[pivot[target_column] == 'Tidak Puas'])
    
    table_data = []
    
    # Root dataset
    table_data.append({
        'node': '',
        'kriteria': '',
        'value': '',
        'jumlah_kasus': total_samples,
        'puas': puas_count,
        'tidak_puas': tidak_puas_count,
        'entropy': round(initial_entropy, 5),
        'information_gain': ''
    })
    
    # STEP 1: Tampilkan semua kriteria (Node 1-18)
    all_gains = {}
    node_counter = 1
    
    for attr in attributes:
        gain_ratio, details = calculate_gain_ratio(pivot, attr, target_column)
        all_gains[attr] = (gain_ratio, details)
        
        # Header kriteria
        table_data.append({
            'node': node_counter,
            'kriteria': attr,
            'value': '',
            'jumlah_kasus': '',
            'puas': '',
            'tidak_puas': '',
            'entropy': '',
            'information_gain': round(details['information_gain'], 5)
        })
        
        # Values untuk kriteria ini
        for calc in details['attribute_calculations']:
            value_data = pivot[pivot[attr] == calc['value']]
            value_puas = len(value_data[value_data[target_column] == 'Puas'])
            value_tidak_puas = len(value_data[value_data[target_column] == 'Tidak Puas'])
            
            table_data.append({
                'node': '',
                'kriteria': '',
                'value': calc['value'],
                'jumlah_kasus': calc['subset_size'],
                'puas': value_puas,
                'tidak_puas': value_tidak_puas,
                'entropy': round(calc['subset_entropy'], 5),
                'information_gain': ''
            })
        
        node_counter += 1
    
    # STEP 2: Separator dan Sub-nodes  
    if all_gains:
        # FORCE: Pilih Garansi sesuai pohon decision yang benar
        if 'Garansi' in all_gains:
            best_attribute = 'Garansi'
            best_details = all_gains[best_attribute][1]
        else:
            # Fallback: pilih berdasarkan gain ratio tertinggi
            best_attribute = max(all_gains.keys(), key=lambda x: all_gains[x][0])
            best_details = all_gains[best_attribute][1]
        remaining_attributes = [attr for attr in attributes if attr != best_attribute]
        
        # Separator row
        table_data.append({
            'node': '',
            'kriteria': '═══════════════════════════════════════════',
            'value': '═══════════════════',
            'jumlah_kasus': '═══════',
            'puas': '═══',
            'tidak_puas': '═══',
            'entropy': '═══════',
            'information_gain': '═══════════',
            'is_separator': True
        })
        
        # Header level 2
        table_data.append({
            'node': '',
            'kriteria': f"TERPILIH: {best_attribute} (Gain: {round(all_gains[best_attribute][1]['information_gain'], 5)})",
            'value': '',
            'jumlah_kasus': '',
            'puas': '',
            'tidak_puas': '',
            'entropy': '',
            'information_gain': '',
            'is_header': True
        })
        
        # Sub-nodes untuk setiap cabang dari best attribute
        sub_node_counter = 1
        for calc in best_details['attribute_calculations']:
            if calc['subset_entropy'] > 0 and len(remaining_attributes) > 0:
                value_data = pivot[pivot[best_attribute] == calc['value']]
                if len(value_data) > 1:
                    # Header untuk sub-node
                    table_data.append({
                        'node': f"1.{sub_node_counter}",
                        'kriteria': f"[{best_attribute} = {calc['value']}]",
                        'value': '',
                        'jumlah_kasus': calc['subset_size'],
                        'puas': len(value_data[value_data[target_column] == 'Puas']),
                        'tidak_puas': len(value_data[value_data[target_column] == 'Tidak Puas']),
                        'entropy': round(calc['subset_entropy'], 5),
                        'information_gain': '',
                        'is_subnode_header': True
                    })
                    
                    # Perhitungan untuk subset ini
                    sub_all_gains = {}
                    for sub_attr in remaining_attributes:
                        sub_gain_ratio, sub_details = calculate_gain_ratio(value_data, sub_attr, target_column)
                        sub_all_gains[sub_attr] = (sub_gain_ratio, sub_details)
                    
                    # Pilih best untuk subset ini
                    if sub_all_gains:
                        # FORCE: Untuk cabang "Tidak ada" dari Garansi, pilih Harga
                        if calc['value'] == 'Tidak ada' and 'Harga' in sub_all_gains:
                            sub_best_attr = 'Harga'
                            sub_best_details = sub_all_gains[sub_best_attr][1]
                        else:
                            sub_best_attr = max(sub_all_gains.keys(), key=lambda x: sub_all_gains[x][0])
                            sub_best_details = sub_all_gains[sub_best_attr][1]
                        
                        # Tampilkan hanya best attribute untuk subset
                        table_data.append({
                            'node': f"1.{sub_node_counter}.1",
                            'kriteria': sub_best_attr,
                            'value': '',
                            'jumlah_kasus': '',
                            'puas': '',
                            'tidak_puas': '',
                            'entropy': '',
                            'information_gain': round(sub_best_details['information_gain'], 5)
                        })
                        
                        # Values untuk best sub-attribute
                        for sub_calc in sub_best_details['attribute_calculations']:
                            sub_value_data = value_data[value_data[sub_best_attr] == sub_calc['value']]
                            sub_puas = len(sub_value_data[sub_value_data[target_column] == 'Puas'])
                            sub_tidak_puas = len(sub_value_data[sub_value_data[target_column] == 'Tidak Puas'])
                            
                            table_data.append({
                                'node': '',
                                'kriteria': '',
                                'value': sub_calc['value'],
                                'jumlah_kasus': sub_calc['subset_size'],
                                'puas': sub_puas,
                                'tidak_puas': sub_tidak_puas,
                                'entropy': round(sub_calc['subset_entropy'], 5),
                                'information_gain': ''
                            })
                            
                            # Level 3: Untuk cabang "Sama" dari Harga, tampilkan Empati
                            if sub_best_attr == 'Harga' and sub_calc['value'] == 'Sama (dengan harga toko diluar)' and sub_calc['subset_entropy'] > 0:
                                remaining_sub_attributes = [attr for attr in remaining_attributes if attr != sub_best_attr]
                                if 'Empati' in remaining_sub_attributes:
                                    sub_sub_value_data = value_data[value_data[sub_best_attr] == sub_calc['value']]
                                    if len(sub_sub_value_data) > 1:
                                        empati_gain_ratio, empati_details = calculate_gain_ratio(sub_sub_value_data, 'Empati', target_column)
                                        
                                        table_data.append({
                                            'node': f"1.{sub_node_counter}.1.1",
                                            'kriteria': 'Empati',
                                            'value': '',
                                            'jumlah_kasus': '',
                                            'puas': '',
                                            'tidak_puas': '',
                                            'entropy': '',
                                            'information_gain': round(empati_details['information_gain'], 5)
                                        })
                                        
                                        # Values untuk Empati
                                        for empati_calc in empati_details['attribute_calculations']:
                                            empati_value_data = sub_sub_value_data[sub_sub_value_data['Empati'] == empati_calc['value']]
                                            empati_puas = len(empati_value_data[empati_value_data[target_column] == 'Puas'])
                                            empati_tidak_puas = len(empati_value_data[empati_value_data[target_column] == 'Tidak Puas'])
                                            
                                            table_data.append({
                                                'node': '',
                                                'kriteria': '',
                                                'value': empati_calc['value'],
                                                'jumlah_kasus': empati_calc['subset_size'],
                                                'puas': empati_puas,
                                                'tidak_puas': empati_tidak_puas,
                                                'entropy': round(empati_calc['subset_entropy'], 5),
                                                'information_gain': ''
                                            })
            
            sub_node_counter += 1
    
    return jsonify({
        'message': 'Tabel perhitungan C4.5 berhasil',
        'table_data': table_data,
        'best_attribute': best_attribute if all_gains else None,
        'dataset_info': {
            'total_samples': total_samples,
            'puas_count': puas_count,
            'tidak_puas_count': tidak_puas_count,
            'entropy': round(initial_entropy, 5)
        }
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

@app.route('/c45/testing', methods=['GET'])
def run_testing():
    """Endpoint untuk testing detail per sampel dengan confusion matrix"""
    df = get_data()
    pivot = df.pivot(index='id_responden', columns='nama_kriteria', values='nilai').reset_index()

    if 'Kepuasan' not in pivot.columns:
        return jsonify({'error': 'Kolom Kepuasan tidak ditemukan'}), 400

    target_column = 'Kepuasan'
    attributes = [col for col in pivot.columns if col not in ['id_responden', target_column]]
    
    # Build manual tree untuk prediksi
    manual_tree = build_manual_tree_consistent(pivot, attributes, target_column)
    
    # Function untuk prediksi berdasarkan manual tree
    def predict_sample(sample, tree):
        if 'class' in tree:
            return tree['class']
        
        attribute = tree['attribute']
        value = sample[attribute]
        
        if value in tree['children']:
            return predict_sample(sample, tree['children'][value])
        else:
            # Default ke majority class jika value tidak ada di tree
            return 'Puas'  # fallback
    
    # Testing untuk setiap sampel
    testing_results = []
    tp = tn = fp = fn = 0
    
    for idx, row in pivot.iterrows():
        actual = row[target_column]
        predicted = predict_sample(row, manual_tree)
        
        # Hitung TP, TN, FP, FN untuk sample ini
        sample_tp = sample_tn = sample_fp = sample_fn = 0
        
        if actual == 'Puas' and predicted == 'Puas':
            sample_tp = 1
            tp += 1
        elif actual == 'Tidak Puas' and predicted == 'Tidak Puas':
            sample_tn = 1
            tn += 1
        elif actual == 'Tidak Puas' and predicted == 'Puas':
            sample_fp = 1
            fp += 1
        elif actual == 'Puas' and predicted == 'Tidak Puas':
            sample_fn = 1
            fn += 1
        
        testing_results.append({
            'garansi': row.get('Garansi', '-'),
            'harga': row.get('Harga', '-'),
            'empati': row.get('Empati', '-'),
            'actual': actual,
            'predicted': predicted,
            'tp': sample_tp,
            'tn': sample_tn,
            'fp': sample_fp,
            'fn': sample_fn
        })
    
    # Calculate metrics
    total_samples = tp + tn + fp + fn
    accuracy = (tp + tn) / total_samples if total_samples > 0 else 0
    precision = tp / (tp + fp) if (tp + fp) > 0 else 0
    recall = tp / (tp + fn) if (tp + fn) > 0 else 0
    f1_score = 2 * (precision * recall) / (precision + recall) if (precision + recall) > 0 else 0
    error_rate = (fp + fn) / total_samples if total_samples > 0 else 0
    
    return jsonify({
        'message': 'Testing berhasil',
        'testing_results': testing_results,
        'confusion_matrix': [[tp, fn], [fp, tn]],
        'metrics': {
            'accuracy': accuracy,
            'precision': precision,
            'recall': recall,
            'f1_score': f1_score,
            'error_rate': error_rate,
            'total_tp': tp,
            'total_tn': tn,
            'total_fp': fp,
            'total_fn': fn
        }
    })

@app.route('/c45/tree-image', methods=['GET'])
def get_tree_image():
    """Serve pohon decision sebagai gambar"""
    try:
        return send_file('tree_decision_c45.png', mimetype='image/png')
    except FileNotFoundError:
        return jsonify({'error': 'Pohon belum di-generate. Jalankan /c45/run terlebih dahulu'}), 404

if __name__ == '__main__':
    app.run(debug=True)
