export const metadata = {
  title: "Incident Timeline",
};

export default function Page({ params }: { params: { id: string } }) {
  return (
    <main>
      <h1>Incident Timeline</h1>
      <p>Chronological timeline for the incident. Currently viewing record {params.id}.</p>
    </main>
  );
}
