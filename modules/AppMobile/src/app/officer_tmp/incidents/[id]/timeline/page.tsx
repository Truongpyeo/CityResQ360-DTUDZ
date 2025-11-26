export const metadata = {
  title: "Incident Timeline",
};

type PageParams = Promise<{ id: string }>;

export default async function Page({ params }: { params: PageParams }) {
  const { id } = await params;

  return (
    <main>
      <h1>Incident Timeline</h1>
      <p>Chronological timeline for the incident. Currently viewing record {id}.</p>
    </main>
  );
}
