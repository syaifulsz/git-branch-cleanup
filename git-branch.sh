# Credit http://stackoverflow.com/a/2514279
for branch in `git branch -r | grep -v HEAD`; do echo -e `git show --format="%ai,%an," $branch | head -n 1` \\t$branch; done | sort -r > git-branch.csv