-- 1. Criação da tabela de Professores
CREATE TABLE IF NOT EXISTS professores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    ano_inicio YEAR NOT NULL
) ENGINE=InnoDB;

-- 2. Criação da tabela de Cursos
-- Define o nome do curso e o período (Manhã, Noite ou AMS como no PDF da Fatec)
CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_curso VARCHAR(100) NOT NULL,
    periodo ENUM('Manhã', 'Noite', 'AMS') NOT NULL
) ENGINE=InnoDB;

-- 3. Criação da tabela de Matérias
-- Cada matéria é vinculada a um curso específico
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(100) NOT NULL,
    id_curso INT NOT NULL,
    FOREIGN KEY (id_curso) REFERENCES cursos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Criação da tabela de Horários (Vínculos)
-- Esta tabela une o professor, a matéria e o curso no tempo e dia específicos
CREATE TABLE IF NOT EXISTS horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_professor INT NOT NULL,
    id_materia INT NOT NULL,
    id_curso INT NOT NULL,
    dia_semana ENUM('Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado') NOT NULL,
    horario_inicio TIME NOT NULL,
    horario_fim TIME NOT NULL,
    FOREIGN KEY (id_professor) REFERENCES professores(id) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materias(id) ON DELETE CASCADE,
    FOREIGN KEY (id_curso) REFERENCES cursos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Tabela de Usuários para o Login
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Inserir um usuário padrão para você conseguir acessar o sistema
-- Login: admin | Senha: 123 (Lembre-se de mudar depois!)
INSERT IGNORE INTO usuarios (login, senha) VALUES ('admin', '123');