export function isPaginationEllipsis(label: string): boolean {
    return label.includes('...');
}

export function paginationLabel(label: string): string {
    return label
        .replace('&laquo;', '')
        .replace('&raquo;', '')
        .replace('Previous', 'Anterior')
        .replace('Next', 'Próxima')
        .trim();
}
